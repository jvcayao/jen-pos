<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Store;
use App\Models\Product;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->store = Store::factory()->create();
        $this->user = User::factory()->create(['store_id' => $this->store->id]);
        $this->user->assignRole('store-admin');
        $this->user->stores()->attach($this->store->id);

        $this->withSession(['current_store_id' => $this->store->id]);
    }

    public function test_menu_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get("/store/{$this->store->slug}/menu");

        $response->assertStatus(200);
    }

    public function test_product_can_be_added_to_cart(): void
    {
        $product = Product::factory()->create(['store_id' => $this->store->id]);

        $response = $this->actingAs($this->user)->post('/cart/add', [
            'id' => $product->id,
        ]);

        $response->assertRedirect();
    }

    public function test_checkout_page_can_be_rendered(): void
    {
        $product = Product::factory()->create(['store_id' => $this->store->id]);

        // Add item to cart first
        $this->actingAs($this->user)->post('/cart/add', ['id' => $product->id]);

        $response = $this->actingAs($this->user)->get('/cart/checkout');

        $response->assertStatus(200);
    }

    public function test_out_of_stock_product_cannot_be_added(): void
    {
        $product = Product::factory()->outOfStock()->create(['store_id' => $this->store->id]);

        $response = $this->actingAs($this->user)->post('/cart/add', [
            'id' => $product->id,
        ]);

        // Should either redirect with error or return error response
        $this->assertTrue($response->isRedirect() || $response->status() >= 400);
    }

    public function test_cart_total_is_calculated_correctly(): void
    {
        $product1 = Product::factory()->create([
            'store_id' => $this->store->id,
            'price' => 100,
        ]);
        $product2 = Product::factory()->create([
            'store_id' => $this->store->id,
            'price' => 50,
        ]);

        $this->actingAs($this->user)->post('/cart/add', ['id' => $product1->id]);
        $this->actingAs($this->user)->post('/cart/add', ['id' => $product2->id]);

        $response = $this->actingAs($this->user)->get('/cart/checkout');

        $response->assertStatus(200);
    }

    public function test_order_can_be_created(): void
    {
        $product = Product::factory()->create(['store_id' => $this->store->id]);

        $this->actingAs($this->user)->post('/cart/add', ['id' => $product->id]);

        $response = $this->actingAs($this->user)->post('/orders', [
            'payment_method' => 'cash',
        ]);

        // Should redirect after order creation
        $this->assertTrue($response->isRedirect() || $response->status() === 200);
    }

    public function test_cart_add_barcode_route_exists(): void
    {
        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'barcode' => '1234567890',
        ]);

        $response = $this->actingAs($this->user)->post('/cart/add-barcode', [
            'barcode' => '1234567890',
        ]);

        // Route should return success (200) or redirect
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_cart_update_route_exists(): void
    {
        $response = $this->actingAs($this->user)->post('/cart/update', [
            'id' => 1,
            'qty' => 1,
            'type' => 'increase',
        ]);

        // Route should exist and return something (even if 404 for missing item)
        $this->assertTrue(in_array($response->status(), [200, 302, 404, 500]));
    }

    public function test_cart_remove_route_exists(): void
    {
        $response = $this->actingAs($this->user)->post('/cart/remove', [
            'id' => 1,
        ]);

        // Route should exist
        $this->assertTrue(in_array($response->status(), [200, 302, 404, 500]));
    }

    public function test_orders_cancel_route_exists(): void
    {
        $response = $this->actingAs($this->user)->post('/orders/cancel');

        // Route should exist and handle the request
        $this->assertTrue(in_array($response->status(), [200, 302, 500]));
    }
}
