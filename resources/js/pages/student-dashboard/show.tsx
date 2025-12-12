import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    ChevronLeft,
    ChevronRight,
    Receipt,
    ShoppingBag,
    Wallet,
} from 'lucide-react';

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(value);
};

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

interface StudentDashboardShowProps {
    student: Student;
    orders: PaginatedOrders;
}

export default function StudentDashboardShow({
    student,
    orders,
}: StudentDashboardShowProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Student Dashboard', href: '/student-dashboard' },
        { title: student.full_name, href: `/student-dashboard/${student.id}` },
    ];

    const totalSpent = orders.data.reduce((sum, order) => sum + order.total, 0);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${student.full_name} - Dashboard`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
                {/* Header */}
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
                            {student.grade_level && ` | ${student.grade_level}`}
                            {student.section && ` - ${student.section}`}
                        </p>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-2">
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
                                {orders.total}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Total spent: {formatCurrency(totalSpent)}
                            </p>
                        </CardContent>
                    </Card>
                </div>

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
