export interface CartItemData {
    id: number;
    name: string;
    price: number;
    qty: number;
    color?: string;
    image?: string;
    has_vat?: boolean;
}

export interface Cart {
    items: CartItemData[];
    subtotal: number;
    vat_amount: number;
    total: number;
    count: number;
    tax_rate: number;
}

export interface CheckoutPageProps {
    cart: Cart;
    [key: string]: unknown;
}

export interface CartItemProps {
    id: string;
    name: string;
    color: string;
    price: number;
    quantity: number;
    image: string;
    onUpdateQuantity: (id: string, quantity: number) => void;
    onRemove: (id: string) => void;
}

export interface OrderSummaryProps {
    total: number; // Total amount (VAT-inclusive)
    vatableSales: number; // Vatable Sales (Net of VAT)
    vatAmount: number; // VAT Amount
    discount: number; // Discount amount
    taxRate: number; // Tax rate (e.g., 0.12)
}

export interface CouponSectionProps {
    onApplyCoupon: (code: string) => void;
}

export interface PaymentMethod {
    id: string;
    name: string;
    logos: string[];
    component: string;
}
