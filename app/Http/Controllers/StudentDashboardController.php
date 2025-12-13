<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Exports\StudentExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class StudentDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('student-dashboard/index');
    }

    public function show(Student $student, Request $request): Response
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : null;
        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : null;

        // Build orders query with optional date filter
        $ordersQuery = $student->orders()->with('items.product');

        if ($startDate && $endDate) {
            $ordersQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $orders = (clone $ordersQuery)
            ->orderByDesc('created_at')
            ->paginate(10)
            ->through(fn ($order) => [
                'id' => $order->id,
                'uuid' => $order->uuid,
                'total' => $order->total,
                'vat' => $order->vat,
                'discount' => $order->discount,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'is_payed' => $order->is_payed,
                'wallet_type' => $order->wallet_type,
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->item,
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'total' => $item->total,
                    'image_url' => $item->product?->image_url,
                ]),
                'created_at' => $order->created_at->format('M d, Y h:i A'),
            ]);

        // Get analytics data
        $analytics = $this->getStudentAnalytics($student, $startDate, $endDate);

        return Inertia::render('student-dashboard/show', [
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'full_name' => $student->full_name,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'grade_level' => $student->grade_level,
                'section' => $student->section,
                'email' => $student->email,
                'phone' => $student->phone,
                'wallet_type' => $student->wallet_type,
                'wallet_balance' => $student->assigned_wallet_balance,
                'has_wallet' => $student->hasAssignedWallet(),
            ],
            'orders' => $orders,
            'analytics' => $analytics,
            'filters' => [
                'start_date' => $startDate?->toDateString() ?? '',
                'end_date' => $endDate?->toDateString() ?? '',
            ],
        ]);
    }

    private function getStudentAnalytics(Student $student, ?Carbon $startDate, ?Carbon $endDate): array
    {
        // Base query for confirmed orders
        $baseQuery = $student->orders()
            ->where('status', 'confirm')
            ->where('is_void', false);

        if ($startDate && $endDate) {
            $baseQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Summary stats
        $totalSpent = (clone $baseQuery)->sum('total');
        $totalOrders = (clone $baseQuery)->count();
        $averageOrderValue = $totalOrders > 0 ? $totalSpent / $totalOrders : 0;

        // Monthly spending trend (last 6 months or within date range)
        $spendingTrendQuery = $student->orders()
            ->where('status', 'confirm')
            ->where('is_void', false);

        if ($startDate && $endDate) {
            $spendingTrendQuery->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            $spendingTrendQuery->where('created_at', '>=', Carbon::now()->subMonths(6));
        }

        $spendingTrend = $spendingTrendQuery
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($item) => [
                'month' => Carbon::createFromFormat('Y-m', $item->month)->format('M Y'),
                'total' => round($item->total, 2),
                'orders' => $item->orders,
            ])
            ->toArray();

        // Category breakdown (top purchased categories)
        $categoryQuery = $student->orders()
            ->where('status', 'confirm')
            ->where('is_void', false);

        if ($startDate && $endDate) {
            $categoryQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Join through taxonomables pivot table (polymorphic relationship)
        $categoryBreakdown = DB::table('orders_items')
            ->join('orders', 'orders_items.order_id', '=', 'orders.id')
            ->leftJoin('products', 'orders_items.product_id', '=', 'products.id')
            ->leftJoin('taxonomables', function ($join) {
                $join->on('products.id', '=', 'taxonomables.taxonomable_id')
                    ->where('taxonomables.taxonomable_type', '=', 'App\\Models\\Product');
            })
            ->leftJoin('taxonomies', 'taxonomables.taxonomy_id', '=', 'taxonomies.id')
            ->where('orders.student_id', $student->id)
            ->where('orders.status', 'confirm')
            ->where('orders.is_void', false)
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('orders.created_at', [$startDate, $endDate]);
            })
            ->select(
                DB::raw("COALESCE(taxonomies.name, 'Uncategorized') as category"),
                DB::raw('SUM(orders_items.total) as total'),
                DB::raw('SUM(orders_items.qty) as quantity')
            )
            ->groupBy('taxonomies.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'category' => $item->category,
                'total' => round($item->total, 2),
                'quantity' => $item->quantity,
            ])
            ->toArray();

        // Top purchased items
        $topItemsQuery = DB::table('orders_items')
            ->join('orders', 'orders_items.order_id', '=', 'orders.id')
            ->where('orders.student_id', $student->id)
            ->where('orders.status', 'confirm')
            ->where('orders.is_void', false);

        if ($startDate && $endDate) {
            $topItemsQuery->whereBetween('orders.created_at', [$startDate, $endDate]);
        }

        $topItems = $topItemsQuery
            ->select(
                'orders_items.item as name',
                DB::raw('SUM(orders_items.qty) as quantity'),
                DB::raw('SUM(orders_items.total) as total')
            )
            ->groupBy('orders_items.item')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->name,
                'quantity' => $item->quantity,
                'total' => round($item->total, 2),
            ])
            ->toArray();

        return [
            'total_spent' => round($totalSpent, 2),
            'total_orders' => $totalOrders,
            'average_order_value' => round($averageOrderValue, 2),
            'spending_trend' => $spendingTrend,
            'category_breakdown' => $categoryBreakdown,
            'top_items' => $topItems,
        ];
    }

    public function searchStudent(Request $request): JsonResponse
    {
        $studentId = $request->query('student_id');

        if (!$studentId) {
            return response()->json(['error' => 'Student ID is required'], 400);
        }

        $student = Student::where('student_id', $studentId)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        if (!$student->is_active) {
            return response()->json(['error' => 'Student account is inactive'], 403);
        }

        return response()->json([
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'full_name' => $student->full_name,
                'grade_level' => $student->grade_level,
                'section' => $student->section,
            ],
        ]);
    }

    public function getOrders(Student $student, Request $request): JsonResponse
    {
        $orders = $student->orders()
            ->with('items.product')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->through(fn ($order) => [
                'id' => $order->id,
                'uuid' => $order->uuid,
                'total' => $order->total,
                'vat' => $order->vat,
                'discount' => $order->discount,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'is_payed' => $order->is_payed,
                'wallet_type' => $order->wallet_type,
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->item,
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'total' => $item->total,
                    'image_url' => $item->product?->image_url,
                ]),
                'created_at' => $order->created_at->format('M d, Y h:i A'),
            ]);

        return response()->json([
            'orders' => $orders,
        ]);
    }

    public function exportExcel(Student $student, Request $request)
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : null;
        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : null;

        $filename = 'student-'.$student->student_id.'-report';
        if ($startDate && $endDate) {
            $filename .= '-'.$startDate->format('Y-m-d').'-to-'.$endDate->format('Y-m-d');
        }
        $filename .= '.xlsx';

        return Excel::download(
            new StudentExport($student, $startDate, $endDate),
            $filename
        );
    }

    public function exportPdf(Student $student, Request $request)
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : null;
        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : null;

        // Get analytics
        $analytics = $this->getStudentAnalytics($student, $startDate, $endDate);

        // Get orders
        $ordersQuery = $student->orders()
            ->with('items')
            ->orderByDesc('created_at');

        if ($startDate && $endDate) {
            $ordersQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $orders = $ordersQuery->limit(50)->get()->map(fn ($order) => [
            'uuid' => $order->uuid,
            'items_count' => $order->items->count(),
            'total' => $order->total,
            'status' => $order->status,
            'payment_method' => $order->payment_method,
            'created_at' => $order->created_at->format('Y-m-d H:i'),
        ])->toArray();

        $period = null;
        if ($startDate && $endDate) {
            $period = $startDate->format('M d, Y').' - '.$endDate->format('M d, Y');
        }

        $filename = 'student-'.$student->student_id.'-report';
        if ($startDate && $endDate) {
            $filename .= '-'.$startDate->format('Y-m-d').'-to-'.$endDate->format('Y-m-d');
        }
        $filename .= '.pdf';

        $pdf = Pdf::loadView('exports.student-pdf', [
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'full_name' => $student->full_name,
                'grade_level' => $student->grade_level,
                'section' => $student->section,
                'email' => $student->email,
                'phone' => $student->phone,
                'wallet_type' => $student->wallet_type,
                'wallet_balance' => $student->hasAssignedWallet() ? $student->assigned_wallet_balance : 0,
            ],
            'analytics' => $analytics,
            'orders' => $orders,
            'period' => $period,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}
