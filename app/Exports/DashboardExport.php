<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DashboardExport implements WithMultipleSheets
{
    protected Carbon $startDate;
    protected Carbon $endDate;
    protected ?string $status;
    protected ?string $paymentMethod;
    protected ?string $walletType;

    public function __construct(
        Carbon $startDate,
        Carbon $endDate,
        ?string $status = null,
        ?string $paymentMethod = null,
        ?string $walletType = null
    ) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
        $this->paymentMethod = $paymentMethod;
        $this->walletType = $walletType;
    }

    public function sheets(): array
    {
        return [
            new SummarySheet($this->startDate, $this->endDate, $this->status, $this->paymentMethod, $this->walletType),
            new OrdersSheet($this->startDate, $this->endDate, $this->status, $this->paymentMethod, $this->walletType),
            new TopProductsSheet($this->startDate, $this->endDate),
        ];
    }
}

class SummarySheet implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    protected Carbon $startDate;
    protected Carbon $endDate;
    protected ?string $status;
    protected ?string $paymentMethod;
    protected ?string $walletType;

    public function __construct(
        Carbon $startDate,
        Carbon $endDate,
        ?string $status = null,
        ?string $paymentMethod = null,
        ?string $walletType = null
    ) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
        $this->paymentMethod = $paymentMethod;
        $this->walletType = $walletType;
    }

    public function collection()
    {
        $query = Order::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', 'confirm')
            ->where('is_void', false);

        if ($this->paymentMethod) {
            $query->where('payment_method', $this->paymentMethod);
        }

        if ($this->walletType) {
            $query->where('payment_method', 'wallet')
                ->where('wallet_type', $this->walletType);
        }

        $totalSales = (clone $query)->sum('total');
        $totalOrders = (clone $query)->count();
        $totalVat = (clone $query)->sum('vat');
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        return collect([
            ['Metric', 'Value'],
            ['Report Period', $this->startDate->format('M d, Y') . ' - ' . $this->endDate->format('M d, Y')],
            ['Total Sales', number_format($totalSales, 2)],
            ['Total Orders', $totalOrders],
            ['Total VAT', number_format($totalVat, 2)],
            ['Average Order Value', number_format($averageOrderValue, 2)],
        ]);
    }

    public function headings(): array
    {
        return ['Sales Report Summary'];
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            'A' => ['font' => ['bold' => true]],
        ];
    }
}

class OrdersSheet implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    protected Carbon $startDate;
    protected Carbon $endDate;
    protected ?string $status;
    protected ?string $paymentMethod;
    protected ?string $walletType;

    public function __construct(
        Carbon $startDate,
        Carbon $endDate,
        ?string $status = null,
        ?string $paymentMethod = null,
        ?string $walletType = null
    ) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
        $this->paymentMethod = $paymentMethod;
        $this->walletType = $walletType;
    }

    public function collection()
    {
        $query = Order::with(['items', 'user', 'cashier'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->paymentMethod) {
            $query->where('payment_method', $this->paymentMethod);
        }

        if ($this->walletType) {
            $query->where('payment_method', 'wallet')
                ->where('wallet_type', $this->walletType);
        }

        return $query->orderByDesc('created_at')
            ->get()
            ->map(fn ($order) => [
                'Order ID' => $order->uuid,
                'Customer' => $order->user?->name ?? 'Walk-in',
                'Cashier' => $order->cashier?->name ?? 'System',
                'Items' => $order->items->count(),
                'Subtotal' => number_format($order->total - $order->vat, 2),
                'VAT' => number_format($order->vat, 2),
                'Discount' => number_format($order->discount, 2),
                'Total' => number_format($order->total, 2),
                'Status' => ucfirst($order->status),
                'Payment Method' => ucfirst($order->payment_method ?? 'N/A'),
                'Wallet Type' => $order->wallet_type ? ucfirst(str_replace('-', ' ', $order->wallet_type)) : 'N/A',
                'Paid' => $order->is_payed ? 'Yes' : 'No',
                'Date' => $order->created_at->format('Y-m-d H:i:s'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Customer',
            'Cashier',
            'Items',
            'Subtotal',
            'VAT',
            'Discount',
            'Total',
            'Status',
            'Payment Method',
            'Wallet Type',
            'Paid',
            'Date',
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

class TopProductsSheet implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    protected Carbon $startDate;
    protected Carbon $endDate;

    public function __construct(Carbon $startDate, Carbon $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return OrderItem::whereHas('order', function ($q) {
            $q->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->where('status', 'confirm')
                ->where('is_void', false);
        })
            ->select(
                'product_id',
                'item',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(total) as total_sales')
            )
            ->groupBy('product_id', 'item')
            ->orderByDesc('total_sales')
            ->limit(20)
            ->get()
            ->map(fn ($item) => [
                'Product Name' => $item->item,
                'Quantity Sold' => $item->total_qty,
                'Total Sales' => number_format($item->total_sales, 2),
            ]);
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'Quantity Sold',
            'Total Sales',
        ];
    }

    public function title(): string
    {
        return 'Top Products';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
