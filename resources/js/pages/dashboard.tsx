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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { endOfMonth, format, startOfMonth, subDays, subMonths } from 'date-fns';
import {
    Calendar,
    ChevronLeft,
    ChevronRight,
    DollarSign,
    Download,
    FileSpreadsheet,
    FileText,
    GraduationCap,
    Package,
    Receipt,
    Search,
    ShoppingCart,
    TrendingDown,
    TrendingUp,
    Users,
    Wallet,
} from 'lucide-react';
import { useRef, useState } from 'react';
import {
    Area,
    AreaChart,
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    Legend,
    Pie,
    PieChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface OrderItem {
    name: string;
    qty: number;
    price: number;
    total: number;
}

interface Order {
    id: number;
    uuid: string;
    customer: string;
    cashier: string;
    total: number;
    vat: number;
    discount: number;
    status: string;
    payment_method: string;
    is_payed: boolean;
    items_count: number;
    items: OrderItem[];
    created_at: string;
}

interface Stats {
    total_sales: number;
    total_orders: number;
    total_vat: number;
    average_order_value: number;
    items_sold: number;
    sales_change: number;
    orders_change: number;
}

interface ChartData {
    period: string;
    sales: number;
    orders: number;
}

interface TopProduct {
    name: string;
    quantity: number;
    sales: number;
}

interface PaymentBreakdown {
    method: string;
    count: number;
    total: number;
    [key: string]: string | number;
}

interface TopStudent {
    student_id: string;
    name: string;
    total_spent: number;
    order_count: number;
}

interface StudentStats {
    total_students: number;
    active_students: number;
    total_wallet_balance: number;
    wallet_sales: number;
    wallet_orders_count: number;
    top_students: TopStudent[];
}

interface PaginatedOrders {
    data: Order[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Filters {
    start_date: string;
    end_date: string;
    search: string;
    status: string;
    payment_method: string;
    wallet_type: string;
}

interface DashboardProps {
    stats: Stats;
    salesChart: ChartData[];
    topProducts: TopProduct[];
    paymentBreakdown: PaymentBreakdown[];
    orders: PaginatedOrders;
    filters: Filters;
    studentStats?: StudentStats;
}

const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884d8'];

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(value);
};

function StatCard({
    title,
    value,
    change,
    icon: Icon,
    format: formatType = 'number',
}: {
    title: string;
    value: number;
    change?: number;
    icon: React.ElementType;
    format?: 'currency' | 'number';
}) {
    const formattedValue =
        formatType === 'currency'
            ? formatCurrency(value)
            : value.toLocaleString();
    const isPositive = change && change >= 0;

    return (
        <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">{title}</CardTitle>
                <Icon className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
                <div className="text-2xl font-bold">{formattedValue}</div>
                {change !== undefined && (
                    <p
                        className={`text-xs ${isPositive ? 'text-green-600' : 'text-red-600'} flex items-center gap-1`}
                    >
                        {isPositive ? (
                            <TrendingUp className="h-3 w-3" />
                        ) : (
                            <TrendingDown className="h-3 w-3" />
                        )}
                        {Math.abs(change)}% from previous period
                    </p>
                )}
            </CardContent>
        </Card>
    );
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
            label: 'Today',
            getValue: () => ({
                start: format(new Date(), 'yyyy-MM-dd'),
                end: format(new Date(), 'yyyy-MM-dd'),
            }),
        },
        {
            label: 'Yesterday',
            getValue: () => ({
                start: format(subDays(new Date(), 1), 'yyyy-MM-dd'),
                end: format(subDays(new Date(), 1), 'yyyy-MM-dd'),
            }),
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
    ];

    const handlePreset = (preset: { start: string; end: string }) => {
        setStart(preset.start);
        setEnd(preset.end);
    };

    const handleApply = () => {
        onApply(start, end);
        setOpen(false);
    };

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button
                    variant="outline"
                    className="justify-start text-left font-normal"
                >
                    <Calendar className="mr-2 h-4 w-4" />
                    {format(new Date(startDate), 'MMM d, yyyy')} -{' '}
                    {format(new Date(endDate), 'MMM d, yyyy')}
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

function OrdersTable({
    orders,
    filters,
    onFilter,
}: {
    orders: PaginatedOrders;
    filters: Filters;
    onFilter: (key: string, value: string) => void;
}) {
    const [search, setSearch] = useState(filters.search);
    const [expandedOrder, setExpandedOrder] = useState<number | null>(null);

    const handleSearch = () => {
        onFilter('search', search);
    };

    const handlePageChange = (url: string | null) => {
        if (url) {
            router.visit(url, { preserveState: true, preserveScroll: true });
        }
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>Orders</CardTitle>
                <CardDescription>
                    Recent orders with filtering options
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div className="mb-4 flex flex-wrap gap-4">
                    <div className="flex flex-1 gap-2">
                        <Input
                            placeholder="Search by Order ID or Customer..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onKeyDown={(e) =>
                                e.key === 'Enter' && handleSearch()
                            }
                            className="max-w-sm"
                        />
                        <Button
                            variant="outline"
                            size="icon"
                            onClick={handleSearch}
                        >
                            <Search className="h-4 w-4" />
                        </Button>
                    </div>
                    <Select
                        value={filters.status || 'all'}
                        onValueChange={(value) =>
                            onFilter('status', value === 'all' ? '' : value)
                        }
                    >
                        <SelectTrigger className="w-[150px]">
                            <SelectValue placeholder="Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Status</SelectItem>
                            <SelectItem value="confirm">Confirmed</SelectItem>
                            <SelectItem value="pending">Pending</SelectItem>
                            <SelectItem value="void">Void</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select
                        value={filters.payment_method || 'all'}
                        onValueChange={(value) =>
                            onFilter(
                                'payment_method',
                                value === 'all' ? '' : value,
                            )
                        }
                    >
                        <SelectTrigger className="w-[150px]">
                            <SelectValue placeholder="Payment" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Methods</SelectItem>
                            <SelectItem value="cash">Cash</SelectItem>
                            <SelectItem value="gcash">G-Cash</SelectItem>
                            <SelectItem value="wallet">
                                Student Wallet
                            </SelectItem>
                            <SelectItem value="card">Card</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select
                        value={filters.wallet_type || 'all'}
                        onValueChange={(value) =>
                            onFilter(
                                'wallet_type',
                                value === 'all' ? '' : value,
                            )
                        }
                    >
                        <SelectTrigger className="w-[180px]">
                            <SelectValue placeholder="Wallet Type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Wallet Types</SelectItem>
                            <SelectItem value="subscribe">
                                Subscribe Wallet
                            </SelectItem>
                            <SelectItem value="non-subscribe">
                                Non-Subscribe Wallet
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div className="overflow-x-auto rounded-md border">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50">
                            <tr>
                                <th className="p-3 text-left font-medium">
                                    Order ID
                                </th>
                                <th className="p-3 text-left font-medium">
                                    Customer
                                </th>
                                <th className="p-3 text-left font-medium">
                                    Cashier
                                </th>
                                <th className="p-3 text-left font-medium">
                                    Items
                                </th>
                                <th className="p-3 text-left font-medium">
                                    Total
                                </th>
                                <th className="p-3 text-left font-medium">
                                    Status
                                </th>
                                <th className="p-3 text-left font-medium">
                                    Payment
                                </th>
                                <th className="p-3 text-left font-medium">
                                    Date
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {orders.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={8}
                                        className="p-8 text-center text-muted-foreground"
                                    >
                                        No orders found
                                    </td>
                                </tr>
                            ) : (
                                orders.data.map((order) => (
                                    <>
                                        <tr
                                            key={order.id}
                                            className="cursor-pointer border-t hover:bg-muted/50"
                                            onClick={() =>
                                                setExpandedOrder(
                                                    expandedOrder === order.id
                                                        ? null
                                                        : order.id,
                                                )
                                            }
                                        >
                                            <td className="p-3 font-mono text-xs">
                                                {order.uuid.slice(0, 8)}...
                                            </td>
                                            <td className="p-3">
                                                {order.customer}
                                            </td>
                                            <td className="p-3">
                                                {order.cashier}
                                            </td>
                                            <td className="p-3">
                                                {order.items_count}
                                            </td>
                                            <td className="p-3 font-medium">
                                                {formatCurrency(order.total)}
                                            </td>
                                            <td className="p-3">
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
                                            </td>
                                            <td className="p-3 capitalize">
                                                {order.payment_method || 'N/A'}
                                            </td>
                                            <td className="p-3">
                                                {order.created_at}
                                            </td>
                                        </tr>
                                        {expandedOrder === order.id && (
                                            <tr
                                                key={`${order.id}-items`}
                                                className="bg-muted/30"
                                            >
                                                <td colSpan={8} className="p-4">
                                                    <div className="space-y-2">
                                                        <p className="text-sm font-medium">
                                                            Order Items:
                                                        </p>
                                                        <div className="grid gap-2">
                                                            {order.items.map(
                                                                (item, idx) => (
                                                                    <div
                                                                        key={
                                                                            idx
                                                                        }
                                                                        className="flex justify-between rounded bg-background p-2 text-sm"
                                                                    >
                                                                        <span>
                                                                            {
                                                                                item.name
                                                                            }{' '}
                                                                            x{' '}
                                                                            {
                                                                                item.qty
                                                                            }
                                                                        </span>
                                                                        <span>
                                                                            {formatCurrency(
                                                                                item.total,
                                                                            )}
                                                                        </span>
                                                                    </div>
                                                                ),
                                                            )}
                                                        </div>
                                                        <div className="flex justify-between border-t pt-2 text-sm">
                                                            <span>VAT:</span>
                                                            <span>
                                                                {formatCurrency(
                                                                    order.vat,
                                                                )}
                                                            </span>
                                                        </div>
                                                        {order.discount > 0 && (
                                                            <div className="flex justify-between text-sm text-green-600">
                                                                <span>
                                                                    Discount:
                                                                </span>
                                                                <span>
                                                                    -
                                                                    {formatCurrency(
                                                                        order.discount,
                                                                    )}
                                                                </span>
                                                            </div>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        )}
                                    </>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {orders.last_page > 1 && (
                    <div className="mt-4 flex items-center justify-between">
                        <p className="text-sm text-muted-foreground">
                            Showing{' '}
                            {(orders.current_page - 1) * orders.per_page + 1} to{' '}
                            {Math.min(
                                orders.current_page * orders.per_page,
                                orders.total,
                            )}{' '}
                            of {orders.total} orders
                        </p>
                        <div className="flex gap-1">
                            <Button
                                variant="outline"
                                size="icon"
                                disabled={orders.current_page === 1}
                                onClick={() =>
                                    handlePageChange(orders.links[0]?.url)
                                }
                            >
                                <ChevronLeft className="h-4 w-4" />
                            </Button>
                            {orders.links.slice(1, -1).map((link) => (
                                <Button
                                    key={link.label}
                                    variant={
                                        link.active ? 'default' : 'outline'
                                    }
                                    size="icon"
                                    onClick={() => handlePageChange(link.url)}
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            ))}
                            <Button
                                variant="outline"
                                size="icon"
                                disabled={
                                    orders.current_page === orders.last_page
                                }
                                onClick={() =>
                                    handlePageChange(
                                        orders.links[orders.links.length - 1]
                                            ?.url,
                                    )
                                }
                            >
                                <ChevronRight className="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

export default function Dashboard({
    stats,
    salesChart,
    topProducts,
    paymentBreakdown,
    orders,
    filters,
    studentStats,
}: DashboardProps) {
    const chartRef = useRef<HTMLDivElement>(null);

    const handleDateChange = (startDate: string, endDate: string) => {
        router.get(
            dashboard().url,
            { ...filters, start_date: startDate, end_date: endDate },
            { preserveState: true, preserveScroll: true },
        );
    };

    const handleFilter = (key: string, value: string) => {
        router.get(
            dashboard().url,
            { ...filters, [key]: value },
            { preserveState: true, preserveScroll: true },
        );
    };

    const exportToExcel = () => {
        const params = new URLSearchParams({
            start_date: filters.start_date,
            end_date: filters.end_date,
            ...(filters.status && { status: filters.status }),
            ...(filters.payment_method && {
                payment_method: filters.payment_method,
            }),
            ...(filters.wallet_type && { wallet_type: filters.wallet_type }),
        });
        window.location.href = `/dashboard/export/excel?${params.toString()}`;
    };

    const exportToPDF = () => {
        const params = new URLSearchParams({
            start_date: filters.start_date,
            end_date: filters.end_date,
            ...(filters.status && { status: filters.status }),
            ...(filters.payment_method && {
                payment_method: filters.payment_method,
            }),
            ...(filters.wallet_type && { wallet_type: filters.wallet_type }),
        });
        window.location.href = `/dashboard/export/pdf?${params.toString()}`;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
                {/* Header with filters */}
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <h1 className="text-2xl font-bold">Sales Dashboard</h1>
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
                    <StatCard
                        title="Total Sales"
                        value={stats.total_sales}
                        change={stats.sales_change}
                        icon={DollarSign}
                        format="currency"
                    />
                    <StatCard
                        title="Total Orders"
                        value={stats.total_orders}
                        change={stats.orders_change}
                        icon={ShoppingCart}
                    />
                    <StatCard
                        title="Items Sold"
                        value={stats.items_sold}
                        icon={Package}
                    />
                    <StatCard
                        title="Average Order Value"
                        value={stats.average_order_value}
                        icon={Receipt}
                        format="currency"
                    />
                </div>

                {/* Student Stats Section */}
                {studentStats && (
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">
                                    Total Students
                                </CardTitle>
                                <Users className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {studentStats.total_students}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    {studentStats.active_students} active
                                </p>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">
                                    Total Wallet Balance
                                </CardTitle>
                                <Wallet className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {formatCurrency(
                                        studentStats.total_wallet_balance,
                                    )}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    Across all students
                                </p>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">
                                    Wallet Sales
                                </CardTitle>
                                <GraduationCap className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {formatCurrency(studentStats.wallet_sales)}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    {studentStats.wallet_orders_count} orders
                                </p>
                            </CardContent>
                        </Card>
                        <Card className="md:col-span-2">
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm font-medium">
                                    Top Students by Spending
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                {studentStats.top_students.length > 0 ? (
                                    <div className="space-y-2">
                                        {studentStats.top_students
                                            .slice(0, 3)
                                            .map((student, idx) => (
                                                <div
                                                    key={student.student_id}
                                                    className="flex items-center justify-between text-sm"
                                                >
                                                    <div className="flex items-center gap-2">
                                                        <span className="flex h-5 w-5 items-center justify-center rounded-full bg-primary/10 text-xs font-medium">
                                                            {idx + 1}
                                                        </span>
                                                        <span className="truncate">
                                                            {student.name}
                                                        </span>
                                                    </div>
                                                    <span className="font-medium">
                                                        {formatCurrency(
                                                            student.total_spent,
                                                        )}
                                                    </span>
                                                </div>
                                            ))}
                                    </div>
                                ) : (
                                    <p className="text-sm text-muted-foreground">
                                        No student purchases yet
                                    </p>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                )}

                {/* Charts Row */}
                <div className="grid gap-4 lg:grid-cols-3">
                    {/* Sales Chart */}
                    <Card className="lg:col-span-2" ref={chartRef}>
                        <CardHeader>
                            <CardTitle>Sales Overview</CardTitle>
                            <CardDescription>
                                Sales and orders over time
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="h-[300px]">
                                {salesChart.length > 0 ? (
                                    <ResponsiveContainer
                                        width="100%"
                                        height="100%"
                                    >
                                        <AreaChart data={salesChart}>
                                            <defs>
                                                <linearGradient
                                                    id="colorSales"
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
                                                dataKey="period"
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
                                                    'Sales',
                                                ]}
                                                contentStyle={{
                                                    backgroundColor:
                                                        'hsl(var(--card))',
                                                    border: '1px solid hsl(var(--border))',
                                                }}
                                            />
                                            <Area
                                                type="monotone"
                                                dataKey="sales"
                                                stroke="#0088FE"
                                                fillOpacity={1}
                                                fill="url(#colorSales)"
                                            />
                                        </AreaChart>
                                    </ResponsiveContainer>
                                ) : (
                                    <div className="flex h-full items-center justify-center text-muted-foreground">
                                        No sales data for this period
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Payment Breakdown */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Payment Methods</CardTitle>
                            <CardDescription>
                                Breakdown by payment type
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="h-[300px]">
                                {paymentBreakdown.length > 0 ? (
                                    <ResponsiveContainer
                                        width="100%"
                                        height="100%"
                                    >
                                        <PieChart>
                                            <Pie
                                                data={paymentBreakdown}
                                                cx="50%"
                                                cy="50%"
                                                innerRadius={60}
                                                outerRadius={80}
                                                paddingAngle={5}
                                                dataKey="total"
                                                nameKey="method"
                                                label={({ method, percent }) =>
                                                    `${method} ${((percent ?? 0) * 100).toFixed(0)}%`
                                                }
                                            >
                                                {paymentBreakdown.map(
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
                                        No payment data
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Top Products */}
                <Card>
                    <CardHeader>
                        <CardTitle>Top Selling Products</CardTitle>
                        <CardDescription>
                            Best performing products by revenue
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="h-[250px]">
                            {topProducts.length > 0 ? (
                                <ResponsiveContainer width="100%" height="100%">
                                    <BarChart
                                        data={topProducts}
                                        layout="vertical"
                                    >
                                        <CartesianGrid
                                            strokeDasharray="3 3"
                                            className="stroke-muted"
                                        />
                                        <XAxis
                                            type="number"
                                            tickFormatter={(value) =>
                                                `₱${value}`
                                            }
                                            className="text-xs"
                                        />
                                        <YAxis
                                            dataKey="name"
                                            type="category"
                                            width={150}
                                            className="text-xs"
                                        />
                                        <Tooltip
                                            formatter={(value: number) =>
                                                formatCurrency(value)
                                            }
                                        />
                                        <Legend />
                                        <Bar
                                            dataKey="sales"
                                            fill="#0088FE"
                                            name="Revenue"
                                            radius={[0, 4, 4, 0]}
                                        />
                                    </BarChart>
                                </ResponsiveContainer>
                            ) : (
                                <div className="flex h-full items-center justify-center text-muted-foreground">
                                    No product data
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>

                {/* Orders Table */}
                <OrdersTable
                    orders={orders}
                    filters={filters}
                    onFilter={handleFilter}
                />
            </div>
        </AppLayout>
    );
}
