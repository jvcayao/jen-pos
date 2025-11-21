import { Button } from '@/components/ui/button';
import { Minus, Plus, Trash2 } from 'lucide-react';

interface CartItemProps {
    id: string;
    name: string;
    color: string;
    price: number;
    quantity: number;
    image: string;
    onUpdateQuantity: (id: string, quantity: number) => void;
    onRemove: (id: string) => void;
}

export const CartItem = ({
    id,
    name,
    color,
    price,
    quantity,
    image,
    onUpdateQuantity,
    onRemove,
}: CartItemProps) => {
    // Ensure price is a number before formatting
    const displayPrice = typeof price === 'number' ? price : Number(price) || 0;
    return (
        <div className="bg-cart-item border-cart-border flex items-center gap-4 rounded-lg border p-4">
            {/* Product Image */}
            <div className="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-lg bg-muted">
                <img
                    src={image}
                    alt={name}
                    className="h-full w-full rounded-lg object-cover"
                />
            </div>

            {/* Product Details */}
            <div className="min-w-0 flex-1">
                <h3 className="line-clamp-2 text-sm font-medium text-foreground lg:text-base">
                    {name}
                </h3>
                <p className="text-cart-color mt-1 text-sm">
                    Color: <span>{color}</span>
                </p>
            </div>

            {/* Quantity Controls */}
            <div className="bg-cart-quantity flex items-center gap-2 rounded-lg p-1">
                <Button
                    variant="ghost"
                    size="icon"
                    className="h-8 w-8 hover:bg-muted"
                    onClick={() =>
                        onUpdateQuantity(id, Math.max(1, quantity - 1))
                    }
                >
                    <Minus className="h-4 w-4" />
                </Button>
                <span className="min-w-[2rem] text-center text-sm font-medium">
                    {quantity}
                </span>
                <Button
                    variant="ghost"
                    size="icon"
                    className="h-8 w-8 hover:bg-muted"
                    onClick={() => onUpdateQuantity(id, quantity + 1)}
                >
                    <Plus className="h-4 w-4" />
                </Button>
            </div>

            {/* Price */}
            <div className="text-cart-price min-w-[80px] text-right text-sm font-semibold lg:text-base">
                ${displayPrice.toFixed(2)}
            </div>

            {/* Remove Button */}
            <Button
                variant="ghost"
                size="icon"
                className="h-8 w-8 text-muted-foreground hover:bg-destructive/10 hover:text-destructive"
                onClick={() => onRemove(id)}
            >
                <Trash2 className="h-4 w-4" />
            </Button>
        </div>
    );
};
