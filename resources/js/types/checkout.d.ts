export interface CartItemData {
    id: number;
    name: string;
    price: number;
    qty: number;
    color?: string;
    image?: string;
}

export interface Cart {
    items: CartItemData[];
    total: number;
    count: number;
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
    subtotal: number;
    discount: number;
    delivery: number;
    tax: number;
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
