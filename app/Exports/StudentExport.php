<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentExport implements WithMultipleSheets
{
    protected Student $student;

    protected ?Carbon $startDate;

    protected ?Carbon $endDate;

    public function __construct(Student $student, ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $this->student = $student;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function sheets(): array
    {
        return [
            new StudentInfoSheet($this->student),
            new StudentOrdersSheet($this->student, $this->startDate, $this->endDate),
            new StudentTopItemsSheet($this->student, $this->startDate, $this->endDate),
        ];
    }
}

class StudentInfoSheet implements FromCollection, WithStyles, WithTitle
{
    protected Student $student;

    public function __construct(Student $student)
    {
        $this->student = $student;
    }

    public function collection()
    {
        return collect([
            ['Field', 'Value'],
            ['Student ID', $this->student->student_id],
            ['Full Name', $this->student->full_name],
            ['Grade Level', $this->student->grade_level ?? 'N/A'],
            ['Section', $this->student->section ?? 'N/A'],
            ['Email', $this->student->email ?? 'N/A'],
            ['Phone', $this->student->phone ?? 'N/A'],
            ['Wallet Type', $this->student->wallet_type ? ucfirst(str_replace('-', ' ', $this->student->wallet_type)) : 'N/A'],
            ['Wallet Balance', $this->student->hasAssignedWallet() ? number_format($this->student->assigned_wallet_balance, 2) : 'N/A'],
            ['Status', $this->student->is_active ? 'Active' : 'Inactive'],
        ]);
    }

    public function title(): string
    {
        return 'Student Info';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            'A' => ['font' => ['bold' => true]],
        ];
    }
}

class StudentOrdersSheet implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected Student $student;

    protected ?Carbon $startDate;

    protected ?Carbon $endDate;

    public function __construct(Student $student, ?Carbon $startDate, ?Carbon $endDate)
    {
        $this->student = $student;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = $this->student->orders()
            ->with('items')
            ->orderByDesc('created_at');

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }

        return $query->get()->map(fn ($order) => [
            'Order ID' => $order->uuid,
            'Date' => $order->created_at->format('Y-m-d H:i:s'),
            'Items' => $order->items->count(),
            'Subtotal' => number_format($order->total - $order->vat, 2),
            'VAT' => number_format($order->vat, 2),
            'Discount' => number_format($order->discount, 2),
            'Total' => number_format($order->total, 2),
            'Status' => ucfirst($order->status),
            'Payment Method' => ucfirst($order->payment_method ?? 'N/A'),
            'Wallet Type' => $order->wallet_type ? ucfirst(str_replace('-', ' ', $order->wallet_type)) : 'N/A',
        ]);
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Date',
            'Items',
            'Subtotal',
            'VAT',
            'Discount',
            'Total',
            'Status',
            'Payment Method',
            'Wallet Type',
        ];
    }

    public function title(): string
    {
        return 'Orders';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class StudentTopItemsSheet implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected Student $student;

    protected ?Carbon $startDate;

    protected ?Carbon $endDate;

    public function __construct(Student $student, ?Carbon $startDate, ?Carbon $endDate)
    {
        $this->student = $student;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = DB::table('orders_items')
            ->join('orders', 'orders_items.order_id', '=', 'orders.id')
            ->where('orders.student_id', $this->student->id)
            ->where('orders.status', 'confirm')
            ->where('orders.is_void', false);

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('orders.created_at', [$this->startDate, $this->endDate]);
        }

        return $query
            ->select(
                'orders_items.item as name',
                DB::raw('SUM(orders_items.qty) as quantity'),
                DB::raw('SUM(orders_items.total) as total')
            )
            ->groupBy('orders_items.item')
            ->orderByDesc('quantity')
            ->limit(20)
            ->get()
            ->map(fn ($item) => [
                'Product Name' => $item->name,
                'Quantity' => $item->quantity,
                'Total Spent' => number_format($item->total, 2),
            ]);
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'Quantity',
            'Total Spent',
        ];
    }

    public function title(): string
    {
        return 'Top Items';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
