import { useState } from 'react';
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { usePage, router } from '@inertiajs/react';
import FlashMessage from '@/components/flash-message';

export default function CheckoutPage() {
  const { cart } = usePage().props;
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [address, setAddress] = useState('');
  const [loading, setLoading] = useState(false);

  function handleCheckout(e: React.FormEvent) {
    e.preventDefault();
    setLoading(true);
    router.post('/checkout', { name, email, address }, {
      onFinish: () => setLoading(false),
    });
  }

  return (
    <div className="min-h-screen bg-muted/40 flex items-center justify-center py-8 px-4">
      <FlashMessage />
      <Card className="w-full max-w-2xl shadow-lg">
        <CardHeader>
          <CardTitle className="text-2xl font-bold">Checkout</CardTitle>
        </CardHeader>
        <CardContent className="grid grid-cols-1 md:grid-cols-2 gap-8">
          <form className="flex flex-col gap-4" onSubmit={handleCheckout}>
            <Input
              placeholder="Full Name"
              value={name}
              onChange={e => setName(e.target.value)}
              required
            />
            <Input
              type="email"
              placeholder="Email Address"
              value={email}
              onChange={e => setEmail(e.target.value)}
              required
            />
            <Input
              placeholder="Shipping Address"
              value={address}
              onChange={e => setAddress(e.target.value)}
              required
            />
            <Button type="submit" className="mt-2" disabled={loading || !cart?.items?.length}>
              {loading ? 'Processing...' : 'Place Order'}
            </Button>
          </form>
          <div className="flex flex-col gap-4">
            <div className="font-semibold text-lg mb-2">Order Summary</div>
            <div className="flex flex-col gap-2">
              {cart?.items?.length ? cart.items.map((item: any) => (
                <div key={item.id} className="flex items-center justify-between border-b pb-2">
                  <div>
                    <div className="font-medium">{item.name}</div>
                    <div className="text-sm text-muted-foreground">Qty: {item.qty}</div>
                  </div>
                  <div className="font-semibold">₱{Number(item.price * item.qty).toFixed(2)}</div>
                </div>
              )) : <div className="text-muted-foreground">Your cart is empty.</div>}
            </div>
            <div className="flex items-center justify-between mt-4 text-base font-semibold">
              <span>Total</span>
              <span>₱{Number(cart?.total || 0).toFixed(2)}</span>
            </div>
          </div>
        </CardContent>
        <CardFooter className="flex justify-center">
          <span className="text-xs text-muted-foreground">By placing your order, you agree to our Terms & Conditions.</span>
        </CardFooter>
      </Card>
    </div>
  );
}
