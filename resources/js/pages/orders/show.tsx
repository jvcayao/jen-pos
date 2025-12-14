import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';
import {
    ArrowLeft,
    CheckCircle,
    Download,
    Package,
    Printer,
} from 'lucide-react';
import { useRef } from 'react';

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
    subtotal: number;
    vat: number;
    discount: number;
    status: string;
    payment_method: string;
    is_payed: boolean;
    notes: string | null;
    customer: string;
    student_id: string | null;
    cashier: string;
    items: OrderItem[];
    created_at: string;
}

interface OrderShowProps {
    order: Order;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Order History', href: '/orders' },
    { title: 'Receipt', href: '#' },
];

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(value);
};

export default function OrderShow({ order }: OrderShowProps) {
    const receiptRef = useRef<HTMLDivElement>(null);

    const handlePrint = () => {
        const printContent = receiptRef.current;
        if (!printContent) return;

        const printWindow = window.open('', '_blank');
        if (!printWindow) return;

        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Receipt - Order #${order.uuid.slice(0, 8).toUpperCase()}</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { font-family: 'Courier New', monospace; padding: 20px; max-width: 300px; margin: 0 auto; }
                    .header { text-align: center; margin-bottom: 20px; }
                    .header h1 { font-size: 18px; margin-bottom: 5px; }
                    .header p { font-size: 12px; color: #666; }
                    .divider { border-top: 1px dashed #ccc; margin: 10px 0; }
                    .info { font-size: 12px; margin-bottom: 5px; }
                    .items { margin: 15px 0; }
                    .item { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 8px; }
                    .item-name { flex: 1; }
                    .item-qty { width: 40px; text-align: center; }
                    .item-price { width: 70px; text-align: right; }
                    .totals { margin-top: 15px; }
                    .total-row { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 5px; }
                    .total-row.grand { font-size: 14px; font-weight: bold; margin-top: 10px; }
                    .footer { text-align: center; margin-top: 20px; font-size: 11px; color: #666; }
                    @media print {
                        body { padding: 0; }
                        @page { margin: 0; size: 80mm auto; }
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>JEN POS</h1>
                    <p>Order Receipt</p>
                </div>
                <div class="divider"></div>
                <div class="info"><strong>Order #:</strong> ${order.uuid.slice(0, 8).toUpperCase()}</div>
                <div class="info"><strong>Date:</strong> ${order.created_at}</div>
                <div class="info"><strong>Customer:</strong> ${order.customer}${order.student_id ? ` (${order.student_id})` : ''}</div>
                <div class="info"><strong>Cashier:</strong> ${order.cashier}</div>
                <div class="info"><strong>Payment:</strong> ${order.payment_method?.toUpperCase() || 'N/A'}</div>
                <div class="divider"></div>
                <div class="items">
                    <div class="item" style="font-weight: bold;">
                        <span class="item-name">Item</span>
                        <span class="item-qty">Qty</span>
                        <span class="item-price">Amount</span>
                    </div>
                    ${order.items
                        .map(
                            (item) => `
                        <div class="item">
                            <span class="item-name">${item.name}</span>
                            <span class="item-qty">${item.qty}</span>
                            <span class="item-price">${formatCurrency(item.total)}</span>
                        </div>
                    `,
                        )
                        .join('')}
                </div>
                <div class="divider"></div>
                <div class="totals">
                    <div class="total-row grand">
                        <span>TOTAL</span>
                        <span>${formatCurrency(order.total + order.discount)}</span>
                    </div>
                    ${
                        order.discount > 0
                            ? `
                        <div class="total-row" style="color: green;">
                            <span>Less: Discount</span>
                            <span>-${formatCurrency(order.discount)}</span>
                        </div>
                        <div class="divider"></div>
                        <div class="total-row grand">
                            <span>AMOUNT DUE</span>
                            <span>${formatCurrency(order.total)}</span>
                        </div>
                    `
                            : ''
                    }
                    <div class="divider"></div>
                    <div class="total-row">
                        <span>Vatable Sales</span>
                        <span>${formatCurrency(order.subtotal)}</span>
                    </div>
                    <div class="total-row">
                        <span>VAT (12%)</span>
                        <span>${formatCurrency(order.vat)}</span>
                    </div>
                </div>
                <div class="divider"></div>
                <div class="footer">
                    <p>Thank you for your purchase!</p>
                    <p style="margin-top: 5px;">Powered by JEN POS</p>
                </div>
            </body>
            </html>
        `);

        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 250);
    };

    const handleDownloadPDF = async () => {
        if (!receiptRef.current) return;

        const canvas = await html2canvas(receiptRef.current, { scale: 2 });
        const imgData = canvas.toDataURL('image/png');

        const pdf = new jsPDF('p', 'mm', [
            80,
            canvas.height * (80 / canvas.width),
        ]);
        pdf.addImage(
            imgData,
            'PNG',
            0,
            0,
            80,
            canvas.height * (80 / canvas.width),
        );
        pdf.save(`receipt-${order.uuid.slice(0, 8)}.pdf`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Order #${order.uuid.slice(0, 8).toUpperCase()}`} />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <Button asChild variant="outline">
                        <Link href="/orders">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back to Orders
                        </Link>
                    </Button>
                    <div className="flex gap-2">
                        <Button variant="outline" onClick={handlePrint}>
                            <Printer className="mr-2 h-4 w-4" />
                            Print
                        </Button>
                        <Button onClick={handleDownloadPDF}>
                            <Download className="mr-2 h-4 w-4" />
                            Download PDF
                        </Button>
                    </div>
                </div>

                <div className="mx-auto w-full max-w-md">
                    <Card ref={receiptRef} className="overflow-hidden">
                        <CardHeader className="bg-primary/5 text-center">
                            <div className="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                                <CheckCircle className="h-6 w-6 text-green-600" />
                            </div>
                            <CardTitle className="text-xl">
                                Order Confirmed
                            </CardTitle>
                            <p className="text-sm text-muted-foreground">
                                Thank you for your purchase!
                            </p>
                        </CardHeader>
                        <CardContent className="space-y-4 p-6">
                            <div className="rounded-lg bg-muted/50 p-4">
                                <div className="grid grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <p className="text-muted-foreground">
                                            Order ID
                                        </p>
                                        <p className="font-mono font-medium">
                                            #
                                            {order.uuid
                                                .slice(0, 8)
                                                .toUpperCase()}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-muted-foreground">
                                            Date
                                        </p>
                                        <p className="font-medium">
                                            {order.created_at}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-muted-foreground">
                                            Customer
                                        </p>
                                        <p className="font-medium">
                                            {order.customer}
                                        </p>
                                        {order.student_id && (
                                            <p className="text-xs text-muted-foreground">
                                                ID: {order.student_id}
                                            </p>
                                        )}
                                    </div>
                                    <div>
                                        <p className="text-muted-foreground">
                                            Cashier
                                        </p>
                                        <p className="font-medium">
                                            {order.cashier}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-muted-foreground">
                                            Payment
                                        </p>
                                        <p className="font-medium capitalize">
                                            {order.payment_method || 'N/A'}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-muted-foreground">
                                            Status
                                        </p>
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
                                </div>
                            </div>

                            <Separator />

                            <div className="space-y-3">
                                <h3 className="font-medium">Order Items</h3>
                                {order.items.map((item) => (
                                    <div
                                        key={item.id}
                                        className="flex items-center gap-3"
                                    >
                                        {item.image_url ? (
                                            <img
                                                src={item.image_url}
                                                alt={item.name}
                                                className="h-12 w-12 rounded-lg object-cover"
                                            />
                                        ) : (
                                            <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-muted">
                                                <Package className="h-6 w-6 text-muted-foreground" />
                                            </div>
                                        )}
                                        <div className="flex-1">
                                            <p className="font-medium">
                                                {item.name}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                {formatCurrency(item.price)} x{' '}
                                                {item.qty}
                                            </p>
                                        </div>
                                        <p className="font-medium">
                                            {formatCurrency(item.total)}
                                        </p>
                                    </div>
                                ))}
                            </div>

                            <Separator />

                            <div className="space-y-2 text-sm">
                                {/* Total Amount (VAT-inclusive) */}
                                <div className="flex justify-between font-semibold">
                                    <span>Total Amount</span>
                                    <span>
                                        {formatCurrency(
                                            order.total + order.discount,
                                        )}
                                    </span>
                                </div>
                                {order.discount > 0 && (
                                    <>
                                        <div className="flex justify-between text-green-600">
                                            <span>Less: Discount</span>
                                            <span>
                                                -
                                                {formatCurrency(order.discount)}
                                            </span>
                                        </div>
                                        <Separator />
                                        <div className="flex justify-between text-lg font-bold">
                                            <span>Amount Due</span>
                                            <span>
                                                {formatCurrency(order.total)}
                                            </span>
                                        </div>
                                    </>
                                )}
                                <Separator />
                                {/* VAT Breakdown */}
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Vatable Sales
                                    </span>
                                    <span>
                                        {formatCurrency(order.subtotal)}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        VAT (12%)
                                    </span>
                                    <span>{formatCurrency(order.vat)}</span>
                                </div>
                                {order.discount === 0 && (
                                    <>
                                        <Separator />
                                        <div className="flex justify-between text-lg font-bold">
                                            <span>Amount Due</span>
                                            <span>
                                                {formatCurrency(order.total)}
                                            </span>
                                        </div>
                                    </>
                                )}
                            </div>

                            {order.notes && (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm text-muted-foreground">
                                            Notes
                                        </p>
                                        <p className="text-sm">{order.notes}</p>
                                    </div>
                                </>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
