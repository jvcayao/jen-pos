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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { formatCurrency } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import {
    ChevronLeft,
    ChevronRight,
    Eye,
    Package,
    Receipt,
    Search,
} from 'lucide-react';
import { memo, useCallback, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Order History', href: '/orders' },
];

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
    notes: string | null;
    customer: string;
    student_id: string | null;
    items: OrderItem[];
    created_at: string;
}

interface PaginatedOrders {
    data: Order[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface OrdersPageProps {
    orders: PaginatedOrders;
    filters: {
        status?: string;
        search?: string;
    };
}

const OrderCard = memo(function OrderCard({ order }: { order: Order }) {
    const [expanded, setExpanded] = useState(false);

    return (
        <Card className="overflow-hidden">
            <CardHeader className="pb-3">
                <div className="flex items-start justify-between">
                    <div>
                        <CardTitle className="text-base font-medium">
                            Order #{order.uuid.slice(0, 8).toUpperCase()}
                        </CardTitle>
                        <CardDescription>{order.created_at}</CardDescription>
                    </div>
                    <Badge
                        variant={
                            order.status === 'confirm'
                                ? 'default'
                                : order.status === 'pending'
                                  ? 'secondary'
                                  : 'destructive'
                        }
                    >
                        {order.status === 'confirm'
                            ? 'Completed'
                            : order.status}
                    </Badge>
                </div>
            </CardHeader>
            <CardContent className="space-y-3">
                <div className="flex items-center justify-between text-sm">
                    <span className="text-muted-foreground">Customer</span>
                    <span>
                        {order.customer}
                        {order.student_id && (
                            <span className="ml-1 text-xs text-muted-foreground">
                                ({order.student_id})
                            </span>
                        )}
                    </span>
                </div>
                <div className="flex items-center justify-between text-sm">
                    <span className="text-muted-foreground">Items</span>
                    <span>{order.items.length} items</span>
                </div>
                <div className="flex items-center justify-between text-sm">
                    <span className="text-muted-foreground">Payment</span>
                    <span className="capitalize">
                        {order.payment_method || 'N/A'}
                    </span>
                </div>
                {order.discount > 0 && (
                    <div className="flex items-center justify-between text-sm text-green-600">
                        <span>Discount</span>
                        <span>-{formatCurrency(order.discount)}</span>
                    </div>
                )}
                <div className="flex items-center justify-between border-t pt-3">
                    <span className="font-medium">Total</span>
                    <span className="text-lg font-bold">
                        {formatCurrency(order.total)}
                    </span>
                </div>

                {expanded && (
                    <div className="space-y-2 border-t pt-3">
                        <p className="text-sm font-medium">Order Items:</p>
                        {order.items.map((item) => (
                            <div
                                key={item.id}
                                className="flex items-center gap-3 rounded-lg bg-muted/50 p-2"
                            >
                                {item.image_url ? (
                                    <img
                                        src={item.image_url}
                                        alt={item.name}
                                        className="h-10 w-10 rounded object-cover"
                                    />
                                ) : (
                                    <div className="flex h-10 w-10 items-center justify-center rounded bg-muted">
                                        <Package className="h-5 w-5 text-muted-foreground" />
                                    </div>
                                )}
                                <div className="flex-1">
                                    <p className="text-sm font-medium">
                                        {item.name}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        {formatCurrency(item.price)} x{' '}
                                        {item.qty}
                                    </p>
                                </div>
                                <p className="text-sm font-medium">
                                    {formatCurrency(item.total)}
                                </p>
                            </div>
                        ))}
                    </div>
                )}

                <div className="flex gap-2 pt-2">
                    <Button
                        variant="outline"
                        size="sm"
                        className="flex-1"
                        onClick={() => setExpanded(!expanded)}
                    >
                        {expanded ? 'Hide Details' : 'Show Details'}
                    </Button>
                    <Button asChild size="sm" className="flex-1">
                        <Link href={`/orders/${order.id}`}>
                            <Eye className="mr-2 h-4 w-4" />
                            View Receipt
                        </Link>
                    </Button>
                </div>
            </CardContent>
        </Card>
    );
});

OrderCard.displayName = 'OrderCard';

export default function OrdersIndex({ orders, filters }: OrdersPageProps) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = () => {
        router.get('/orders', { ...filters, search }, { preserveState: true });
    };

    const handleStatusFilter = (status: string) => {
        router.get(
            '/orders',
            { ...filters, status: status === 'all' ? '' : status },
            { preserveState: true },
        );
    };

    const handlePageChange = (url: string | null) => {
        if (url) {
            router.visit(url, { preserveState: true });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Order History" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Order History</h1>
                        <p className="text-muted-foreground">
                            View your past orders and receipts
                        </p>
                    </div>
                    <Receipt className="h-8 w-8 text-muted-foreground" />
                </div>

                <div className="flex flex-wrap gap-4">
                    <div className="flex flex-1 gap-2">
                        <Input
                            placeholder="Search by Order ID..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onKeyDown={(e) =>
                                e.key === 'Enter' && handleSearch()
                            }
                            className="max-w-xs"
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
                        onValueChange={handleStatusFilter}
                    >
                        <SelectTrigger className="w-[150px]">
                            <SelectValue placeholder="Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Status</SelectItem>
                            <SelectItem value="confirm">Completed</SelectItem>
                            <SelectItem value="pending">Pending</SelectItem>
                            <SelectItem value="void">Void</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                {orders.data.length === 0 ? (
                    <Card className="py-12">
                        <CardContent className="flex flex-col items-center justify-center text-center">
                            <Receipt className="mb-4 h-12 w-12 text-muted-foreground" />
                            <h3 className="text-lg font-medium">
                                No orders found
                            </h3>
                            <p className="text-muted-foreground">
                                You haven't placed any orders yet.
                            </p>
                            <Button asChild className="mt-4">
                                <Link href="/menu">Start Shopping</Link>
                            </Button>
                        </CardContent>
                    </Card>
                ) : (
                    <>
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            {orders.data.map((order) => (
                                <OrderCard key={order.id} order={order} />
                            ))}
                        </div>

                        {orders.last_page > 1 && (
                            <div className="flex items-center justify-between">
                                <p className="text-sm text-muted-foreground">
                                    Showing{' '}
                                    {(orders.current_page - 1) *
                                        orders.per_page +
                                        1}{' '}
                                    to{' '}
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
                                            handlePageChange(
                                                orders.links[0]?.url,
                                            )
                                        }
                                    >
                                        <ChevronLeft className="h-4 w-4" />
                                    </Button>
                                    {orders.links.slice(1, -1).map((link) => (
                                        <Button
                                            key={link.label}
                                            variant={
                                                link.active
                                                    ? 'default'
                                                    : 'outline'
                                            }
                                            size="icon"
                                            onClick={() =>
                                                handlePageChange(link.url)
                                            }
                                        >
                                            {link.label.replace(/&laquo;|&raquo;/g, '')}
                                        </Button>
                                    ))}
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        disabled={
                                            orders.current_page ===
                                            orders.last_page
                                        }
                                        onClick={() =>
                                            handlePageChange(
                                                orders.links[
                                                    orders.links.length - 1
                                                ]?.url,
                                            )
                                        }
                                    >
                                        <ChevronRight className="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        )}
                    </>
                )}
            </div>
        </AppLayout>
    );
}
