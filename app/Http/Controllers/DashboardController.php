<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::now()->endOfDay();

        // Get summary statistics
        $stats = $this->getStats($startDate, $endDate);

        // Get sales chart data
        $salesChart = $this->getSalesChartData($startDate, $endDate);

        // Get top products
        $topProducts = $this->getTopProducts($startDate, $endDate);

        // Get payment method breakdown
        $paymentBreakdown = $this->getPaymentBreakdown($startDate, $endDate);

        // Get recent orders with pagination
        $orders = $this->getOrders($request, $startDate, $endDate);

        return Inertia::render('dashboard', [
            'stats' => $stats,
            'salesChart' => $salesChart,
            'topProducts' => $topProducts,
            'paymentBreakdown' => $paymentBreakdown,
            'orders' => $orders,
            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'search' => $request->input('search', ''),
                'status' => $request->input('status', ''),
                'payment_method' => $request->input('payment_method', ''),
            ],
        ]);
    }

    private function getStats(Carbon $startDate, Carbon $endDate): array
    {
        $baseQuery = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'confirm')
            ->where('is_void', false);

        $totalSales = (clone $baseQuery)->sum('total');
        $totalOrders = (clone $baseQuery)->count();
        $totalVat = (clone $baseQuery)->sum('vat');
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        // Get previous period for comparison
        $periodDays = $startDate->diffInDays($endDate) + 1;
        $prevStartDate = (clone $startDate)->subDays($periodDays);
        $prevEndDate = (clone $startDate)->subDay();

        $prevQuery = Order::whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->where('status', 'confirm')
            ->where('is_void', false);

        $prevTotalSales = $prevQuery->sum('total');
        $prevTotalOrders = $prevQuery->count();

        // Calculate percentage changes
        $salesChange = $prevTotalSales > 0
            ? (($totalSales - $prevTotalSales) / $prevTotalSales) * 100
            : ($totalSales > 0 ? 100 : 0);

        $ordersChange = $prevTotalOrders > 0
            ? (($totalOrders - $prevTotalOrders) / $prevTotalOrders) * 100
            : ($totalOrders > 0 ? 100 : 0);

        // Items sold
        $itemsSold = OrderItem::whereHas('order', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'confirm')
                ->where('is_void', false);
        })->sum('qty');

        return [
            'total_sales' => round($totalSales, 2),
            'total_orders' => $totalOrders,
            'total_vat' => round($totalVat, 2),
            'average_order_value' => round($averageOrderValue, 2),
            'items_sold' => $itemsSold,
            'sales_change' => round($salesChange, 1),
            'orders_change' => round($ordersChange, 1),
        ];
    }

    private function getSalesChartData(Carbon $startDate, Carbon $endDate): array
    {
        $days = $startDate->diffInDays($endDate);

        // Determine grouping interval
        if ($days <= 1) {
            $format = 'H:00';
            $groupBy = 'hour';
        } elseif ($days <= 31) {
            $format = 'M d';
            $groupBy = 'day';
        } elseif ($days <= 365) {
            $format = 'M Y';
            $groupBy = 'month';
        } else {
            $format = 'Y';
            $groupBy = 'year';
        }

        $dateFormat = match ($groupBy) {
            'hour' => '%Y-%m-%d %H:00:00',
            'day' => '%Y-%m-%d',
            'month' => '%Y-%m',
            'year' => '%Y',
        };

        $sales = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'confirm')
            ->where('is_void', false)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
                DB::raw('SUM(total) as sales'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return $sales->map(function ($item) use ($groupBy, $format) {
            $date = match ($groupBy) {
                'hour' => Carbon::parse($item->period),
                'day' => Carbon::parse($item->period),
                'month' => Carbon::createFromFormat('Y-m', $item->period),
                'year' => Carbon::createFromFormat('Y', $item->period),
            };

            return [
                'period' => $date->format($format),
                'sales' => round($item->sales, 2),
                'orders' => $item->orders,
            ];
        })->toArray();
    }

    private function getTopProducts(Carbon $startDate, Carbon $endDate, int $limit = 5): array
    {
        return OrderItem::whereHas('order', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate])
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
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->item,
                'quantity' => $item->total_qty,
                'sales' => round($item->total_sales, 2),
            ])
            ->toArray();
    }

    private function getPaymentBreakdown(Carbon $startDate, Carbon $endDate): array
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'confirm')
            ->where('is_void', false)
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('payment_method')
            ->get()
            ->map(fn ($item) => [
                'method' => ucfirst($item->payment_method ?? 'Unknown'),
                'count' => $item->count,
                'total' => round($item->total, 2),
            ])
            ->toArray();
    }

    private function getOrders(Request $request, Carbon $startDate, Carbon $endDate)
    {
        $query = Order::with(['items', 'user', 'cashier'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('uuid', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        return $query->orderByDesc('created_at')
            ->paginate(15)
            ->through(fn ($order) => [
                'id' => $order->id,
                'uuid' => $order->uuid,
                'customer' => $order->user?->name ?? 'Walk-in',
                'cashier' => $order->cashier?->name ?? 'System',
                'total' => $order->total,
                'vat' => $order->vat,
                'discount' => $order->discount,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'is_payed' => $order->is_payed,
                'items_count' => $order->items->count(),
                'items' => $order->items->map(fn ($item) => [
                    'name' => $item->item,
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'total' => $item->total,
                ]),
                'created_at' => $order->created_at->format('Y-m-d H:i'),
            ]);
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::now()->endOfDay();

        $query = Order::with(['items', 'user', 'cashier'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        $orders = $query->orderByDesc('created_at')->get();

        return response()->json([
            'orders' => $orders->map(fn ($order) => [
                'Order ID' => $order->uuid,
                'Customer' => $order->user?->name ?? 'Walk-in',
                'Cashier' => $order->cashier?->name ?? 'System',
                'Items' => $order->items->count(),
                'Subtotal' => $order->total - $order->vat,
                'VAT' => $order->vat,
                'Discount' => $order->discount,
                'Total' => $order->total,
                'Status' => ucfirst($order->status),
                'Payment Method' => ucfirst($order->payment_method ?? 'N/A'),
                'Paid' => $order->is_payed ? 'Yes' : 'No',
                'Date' => $order->created_at->format('Y-m-d H:i:s'),
            ]),
            'summary' => [
                'total_sales' => $orders->where('status', 'confirm')->where('is_void', false)->sum('total'),
                'total_orders' => $orders->where('status', 'confirm')->where('is_void', false)->count(),
                'total_vat' => $orders->where('status', 'confirm')->where('is_void', false)->sum('vat'),
                'period' => $startDate->format('M d, Y').' - '.$endDate->format('M d, Y'),
            ],
        ]);
    }
}
