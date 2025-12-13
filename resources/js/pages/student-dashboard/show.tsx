import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { endOfMonth, format, startOfMonth, subDays, subMonths } from 'date-fns';
import {
    ArrowLeft,
    Calendar,
    ChevronLeft,
    ChevronRight,
    Download,
    FileSpreadsheet,
    FileText,
    Receipt,
    ShoppingBag,
    TrendingUp,
    Wallet,
} from 'lucide-react';
import { useState } from 'react';
import {
    Area,
    AreaChart,
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    Pie,
    PieChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(value);
};

const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884d8'];

interface OrderItem {
    id: number;
    name: string;
    qty: number;
    price: number;
    total: number;
    image_url: string | null;
}

interface Order {
    id: number;
    uuid: string;
    total: number;
    vat: number;
    discount: number;
    status: string;
    payment_method: string;
    is_payed: boolean;
    wallet_type: string | null;
    items: OrderItem[];
    created_at: string;
}

interface Student {
    id: number;
    student_id: string;
    full_name: string;
    first_name: string;
    last_name: string;
    grade_level: string | null;
    section: string | null;
    email: string | null;
    phone: string | null;
    wallet_type: string | null;
    wallet_balance: number;
    has_wallet: boolean;
}

interface PaginatedOrders {
    data: Order[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface SpendingTrend {
    month: string;
    total: number;
    orders: number;
}

interface CategoryBreakdown {
    category: string;
    total: number;
    quantity: number;
}

interface TopItem {
    name: string;
    quantity: number;
    total: number;
}

interface Analytics {
    total_spent: number;
    total_orders: number;
    average_order_value: number;
    spending_trend: SpendingTrend[];
    category_breakdown: CategoryBreakdown[];
    top_items: TopItem[];
}

interface Filters {
    start_date: string;
    end_date: string;
}

interface StudentDashboardShowProps {
    student: Student;
    orders: PaginatedOrders;
    analytics: Analytics;
    filters: Filters;
}

function DateRangePicker({
    startDate,
    endDate,
    onApply,
}: {
    startDate: string;
    endDate: string;
    onApply: (start: string, end: string) => void;
}) {
    const [start, setStart] = useState(startDate);
    const [end, setEnd] = useState(endDate);
    const [open, setOpen] = useState(false);

    const presets = [
        {
            label: 'All Time',
            getValue: () => ({ start: '', end: '' }),
        },
        {
            label: 'Last 7 days',
            getValue: () => ({
                start: format(subDays(new Date(), 6), 'yyyy-MM-dd'),
                end: format(new Date(), 'yyyy-MM-dd'),
            }),
        },
        {
            label: 'Last 30 days',
            getValue: () => ({
                start: format(subDays(new Date(), 29), 'yyyy-MM-dd'),
                end: format(new Date(), 'yyyy-MM-dd'),
            }),
        },
        {
            label: 'This month',
            getValue: () => ({
                start: format(startOfMonth(new Date()), 'yyyy-MM-dd'),
                end: format(endOfMonth(new Date()), 'yyyy-MM-dd'),
            }),
        },
        {
            label: 'Last month',
            getValue: () => ({
                start: format(
                    startOfMonth(subMonths(new Date(), 1)),
                    'yyyy-MM-dd',
                ),
                end: format(endOfMonth(subMonths(new Date(), 1)), 'yyyy-MM-dd'),
            }),
        },
        {
            label: 'Last 3 months',
            getValue: () => ({
                start: format(subMonths(new Date(), 3), 'yyyy-MM-dd'),
                end: format(new Date(), 'yyyy-MM-dd'),
            }),
        },
    ];

    const handlePreset = (preset: { start: string; end: string }) => {
        setStart(preset.start);
        setEnd(preset.end);
    };

    const handleApply = () => {
        onApply(start, end);
        setOpen(false);
    };

    const displayText =
        startDate && endDate
            ? `${format(new Date(startDate), 'MMM d, yyyy')} - ${format(new Date(endDate), 'MMM d, yyyy')}`
            : 'All Time';

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button
                    variant="outline"
                    className="justify-start text-left font-normal"
                >
                    <Calendar className="mr-2 h-4 w-4" />
                    {displayText}
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-auto p-4" align="end">
                <div className="space-y-4">
                    <div className="flex flex-wrap gap-2">
                        {presets.map((preset) => (
                            <Button
                                key={preset.label}
                                variant="outline"
                                size="sm"
                                onClick={() => handlePreset(preset.getValue())}
                            >
                                {preset.label}
                            </Button>
                        ))}
                    </div>
                    <div className="grid grid-cols-2 gap-2">
                        <div>
                            <label className="text-sm font-medium">
                                Start Date
                            </label>
                            <Input
                                type="date"
                                value={start}
                                onChange={(e) => setStart(e.target.value)}
                            />
                        </div>
                        <div>
                            <label className="text-sm font-medium">
                                End Date
                            </label>
                            <Input
                                type="date"
                                value={end}
                                onChange={(e) => setEnd(e.target.value)}
                            />
                        </div>
                    </div>
                    <Button onClick={handleApply} className="w-full">
                        Apply
                    </Button>
                </div>
            </PopoverContent>
        </Popover>
    );
}

export default function StudentDashboardShow({
    student,
    orders,
    analytics,
    filters,
}: StudentDashboardShowProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Student Dashboard', href: '/student-dashboard' },
        { title: student.full_name, href: `/student-dashboard/${student.id}` },
    ];

    const handleDateChange = (startDate: string, endDate: string) => {
        router.get(
            `/student-dashboard/${student.id}`,
            {
                ...(startDate && { start_date: startDate }),
                ...(endDate && { end_date: endDate }),
            },
            { preserveState: true, preserveScroll: true },
        );
    };

    const exportToExcel = () => {
        const params = new URLSearchParams({
            ...(filters.start_date && { start_date: filters.start_date }),
            ...(filters.end_date && { end_date: filters.end_date }),
        });
        window.location.href = `/student-dashboard/${student.id}/export/excel?${params.toString()}`;
    };

    const exportToPDF = () => {
        const params = new URLSearchParams({
            ...(filters.start_date && { start_date: filters.start_date }),
            ...(filters.end_date && { end_date: filters.end_date }),
        });
        window.location.href = `/student-dashboard/${student.id}/export/pdf?${params.toString()}`;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${student.full_name} - Dashboard`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
                {/* Header */}
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div className="flex items-center gap-4">
                        <Link href="/student-dashboard">
                            <Button variant="ghost" size="icon">
                                <ArrowLeft className="h-5 w-5" />
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold">
                                {student.full_name}
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                {student.student_id}
                                {student.grade_level &&
                                    ` | ${student.grade_level}`}
                                {student.section && ` - ${student.section}`}
                            </p>
                        </div>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                        <DateRangePicker
                            startDate={filters.start_date}
                            endDate={filters.end_date}
                            onApply={handleDateChange}
                        />
                        <Popover>
                            <PopoverTrigger asChild>
                                <Button variant="outline">
                                    <Download className="mr-2 h-4 w-4" />
                                    Export
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent className="w-48" align="end">
                                <div className="flex flex-col gap-2">
                                    <Button
                                        variant="ghost"
                                        className="justify-start"
                                        onClick={exportToExcel}
                                    >
                                        <FileSpreadsheet className="mr-2 h-4 w-4" />
                                        Export to Excel
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        className="justify-start"
                                        onClick={exportToPDF}
                                    >
                                        <FileText className="mr-2 h-4 w-4" />
                                        Export to PDF
                                    </Button>
                                </div>
                            </PopoverContent>
                        </Popover>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                <Wallet className="h-4 w-4" />
                                Wallet Balance
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {student.wallet_type ? (
                                <>
                                    <div className="text-2xl font-bold">
                                        {formatCurrency(student.wallet_balance)}
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        {student.wallet_type === 'subscribe'
                                            ? 'Subscribe'
                                            : 'Non-Subscribe'}{' '}
                                        Wallet
                                    </p>
                                </>
                            ) : (
                                <p className="text-sm text-muted-foreground">
                                    No wallet assigned
                                </p>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                <ShoppingBag className="h-4 w-4" />
                                Total Orders
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {analytics.total_orders}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                {filters.start_date
                                    ? 'In selected period'
                                    : 'All time'}
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                <Receipt className="h-4 w-4" />
                                Total Spent
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {formatCurrency(analytics.total_spent)}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                {filters.start_date
                                    ? 'In selected period'
                                    : 'All time'}
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                <TrendingUp className="h-4 w-4" />
                                Avg. Order Value
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {formatCurrency(analytics.average_order_value)}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Per order
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Charts Row */}
                <div className="grid gap-4 lg:grid-cols-2">
                    {/* Spending Trend Chart */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Spending Trend</CardTitle>
                            <CardDescription>
                                Monthly spending over time
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="h-[250px]">
                                {analytics.spending_trend.length > 0 ? (
                                    <ResponsiveContainer
                                        width="100%"
                                        height="100%"
                                    >
                                        <AreaChart
                                            data={analytics.spending_trend}
                                        >
                                            <defs>
                                                <linearGradient
                                                    id="colorSpending"
                                                    x1="0"
                                                    y1="0"
                                                    x2="0"
                                                    y2="1"
                                                >
                                                    <stop
                                                        offset="5%"
                                                        stopColor="#0088FE"
                                                        stopOpacity={0.8}
                                                    />
                                                    <stop
                                                        offset="95%"
                                                        stopColor="#0088FE"
                                                        stopOpacity={0}
                                                    />
                                                </linearGradient>
                                            </defs>
                                            <CartesianGrid
                                                strokeDasharray="3 3"
                                                className="stroke-muted"
                                            />
                                            <XAxis
                                                dataKey="month"
                                                className="text-xs"
                                            />
                                            <YAxis
                                                className="text-xs"
                                                tickFormatter={(value) =>
                                                    `₱${value}`
                                                }
                                            />
                                            <Tooltip
                                                formatter={(value: number) => [
                                                    formatCurrency(value),
                                                    'Spent',
                                                ]}
                                                contentStyle={{
                                                    backgroundColor:
                                                        'hsl(var(--card))',
                                                    border: '1px solid hsl(var(--border))',
                                                }}
                                            />
                                            <Area
                                                type="monotone"
                                                dataKey="total"
                                                stroke="#0088FE"
                                                fillOpacity={1}
                                                fill="url(#colorSpending)"
                                            />
                                        </AreaChart>
                                    </ResponsiveContainer>
                                ) : (
                                    <div className="flex h-full items-center justify-center text-muted-foreground">
                                        No spending data available
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Category Breakdown */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Category Breakdown</CardTitle>
                            <CardDescription>
                                Spending by product category
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="h-[250px]">
                                {analytics.category_breakdown.length > 0 ? (
                                    <ResponsiveContainer
                                        width="100%"
                                        height="100%"
                                    >
                                        <PieChart>
                                            <Pie
                                                data={
                                                    analytics.category_breakdown
                                                }
                                                cx="50%"
                                                cy="50%"
                                                innerRadius={50}
                                                outerRadius={80}
                                                paddingAngle={5}
                                                dataKey="total"
                                                nameKey="category"
                                                label={({
                                                    category,
                                                    percent,
                                                }) =>
                                                    `${category} ${((percent ?? 0) * 100).toFixed(0)}%`
                                                }
                                            >
                                                {analytics.category_breakdown.map(
                                                    (_, index) => (
                                                        <Cell
                                                            key={`cell-${index}`}
                                                            fill={
                                                                COLORS[
                                                                    index %
                                                                        COLORS.length
                                                                ]
                                                            }
                                                        />
                                                    ),
                                                )}
                                            </Pie>
                                            <Tooltip
                                                formatter={(value: number) =>
                                                    formatCurrency(value)
                                                }
                                            />
                                        </PieChart>
                                    </ResponsiveContainer>
                                ) : (
                                    <div className="flex h-full items-center justify-center text-muted-foreground">
                                        No category data available
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Top Items */}
                <Card>
                    <CardHeader>
                        <CardTitle>Most Purchased Items</CardTitle>
                        <CardDescription>
                            Top 5 frequently purchased products
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="h-[200px]">
                            {analytics.top_items.length > 0 ? (
                                <ResponsiveContainer width="100%" height="100%">
                                    <BarChart
                                        data={analytics.top_items}
                                        layout="vertical"
                                    >
                                        <CartesianGrid
                                            strokeDasharray="3 3"
                                            className="stroke-muted"
                                        />
                                        <XAxis
                                            type="number"
                                            className="text-xs"
                                        />
                                        <YAxis
                                            dataKey="name"
                                            type="category"
                                            width={150}
                                            className="text-xs"
                                        />
                                        <Tooltip
                                            formatter={(
                                                value: number,
                                                name: string,
                                            ) => [
                                                name === 'quantity'
                                                    ? value
                                                    : formatCurrency(value),
                                                name === 'quantity'
                                                    ? 'Quantity'
                                                    : 'Total',
                                            ]}
                                        />
                                        <Bar
                                            dataKey="quantity"
                                            fill="#0088FE"
                                            name="Quantity"
                                            radius={[0, 4, 4, 0]}
                                        />
                                    </BarChart>
                                </ResponsiveContainer>
                            ) : (
                                <div className="flex h-full items-center justify-center text-muted-foreground">
                                    No purchase data available
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>

                {/* Order History */}
                <Card className="flex-1">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Receipt className="h-5 w-5" />
                            Order History
                        </CardTitle>
                        <CardDescription>
                            View all orders made by this student
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {orders.data.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <ShoppingBag className="mb-4 h-12 w-12 text-muted-foreground" />
                                <p className="text-muted-foreground">
                                    No orders yet
                                </p>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {orders.data.map((order) => (
                                    <div
                                        key={order.id}
                                        className="rounded-lg border p-4"
                                    >
                                        <div className="flex items-start justify-between">
                                            <div>
                                                <div className="flex items-center gap-2">
                                                    <span className="font-medium">
                                                        Order #
                                                        {order.uuid.slice(0, 8)}
                                                    </span>
                                                    <Badge
                                                        variant={
                                                            order.status ===
                                                            'confirm'
                                                                ? 'default'
                                                                : order.status ===
                                                                    'pending'
                                                                  ? 'secondary'
                                                                  : 'destructive'
                                                        }
                                                    >
                                                        {order.status}
                                                    </Badge>
                                                    {order.payment_method ===
                                                        'wallet' &&
                                                        order.wallet_type && (
                                                            <Badge variant="outline">
                                                                {order.wallet_type ===
                                                                'subscribe'
                                                                    ? 'Subscribe'
                                                                    : 'Non-Subscribe'}{' '}
                                                                Wallet
                                                            </Badge>
                                                        )}
                                                </div>
                                                <p className="text-sm text-muted-foreground">
                                                    {order.created_at}
                                                </p>
                                            </div>
                                            <div className="text-right">
                                                <div className="text-lg font-bold">
                                                    {formatCurrency(
                                                        order.total,
                                                    )}
                                                </div>
                                                <p className="text-xs text-muted-foreground">
                                                    {order.payment_method}
                                                </p>
                                            </div>
                                        </div>

                                        {/* Order Items */}
                                        <div className="mt-3 space-y-2 border-t pt-3">
                                            {order.items.map((item) => (
                                                <div
                                                    key={item.id}
                                                    className="flex items-center justify-between text-sm"
                                                >
                                                    <div className="flex items-center gap-2">
                                                        {item.image_url ? (
                                                            <img
                                                                src={
                                                                    item.image_url
                                                                }
                                                                alt={item.name}
                                                                className="h-8 w-8 rounded object-cover"
                                                            />
                                                        ) : (
                                                            <div className="flex h-8 w-8 items-center justify-center rounded bg-muted">
                                                                <ShoppingBag className="h-4 w-4 text-muted-foreground" />
                                                            </div>
                                                        )}
                                                        <span>
                                                            {item.name} x{' '}
                                                            {item.qty}
                                                        </span>
                                                    </div>
                                                    <span className="text-muted-foreground">
                                                        {formatCurrency(
                                                            item.total,
                                                        )}
                                                    </span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                ))}

                                {/* Pagination */}
                                {orders.last_page > 1 && (
                                    <div className="flex items-center justify-between pt-4">
                                        <p className="text-sm text-muted-foreground">
                                            Showing{' '}
                                            {(orders.current_page - 1) *
                                                orders.per_page +
                                                1}{' '}
                                            to{' '}
                                            {Math.min(
                                                orders.current_page *
                                                    orders.per_page,
                                                orders.total,
                                            )}{' '}
                                            of {orders.total} orders
                                        </p>
                                        <div className="flex gap-1">
                                            <Button
                                                variant="outline"
                                                size="icon"
                                                disabled={
                                                    orders.current_page === 1
                                                }
                                                asChild
                                            >
                                                <Link
                                                    href={
                                                        orders.links[0]?.url ||
                                                        '#'
                                                    }
                                                >
                                                    <ChevronLeft className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <Button
                                                variant="outline"
                                                size="icon"
                                                disabled={
                                                    orders.current_page ===
                                                    orders.last_page
                                                }
                                                asChild
                                            >
                                                <Link
                                                    href={
                                                        orders.links[
                                                            orders.links
                                                                .length - 1
                                                        ]?.url || '#'
                                                    }
                                                >
                                                    <ChevronRight className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                        </div>
                                    </div>
                                )}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
