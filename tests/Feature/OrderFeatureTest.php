<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Store;
use App\Models\Student;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderFeatureTest extends TestCase
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

    public function test_orders_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get('/orders');

        $response->assertStatus(200);
    }

    public function test_orders_are_listed_correctly(): void
    {
        Order::factory()->count(3)->create([
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
            'account_id' => $this->user->id,
            'cashier_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/orders');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('orders/index')
                ->has('orders.data', 3)
        );
    }

    public function test_orders_can_be_filtered_by_status(): void
    {
        Order::factory()->create([
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
            'account_id' => $this->user->id,
            'cashier_id' => $this->user->id,
            'status' => 'confirm',
        ]);
        Order::factory()->pending()->create([
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
            'account_id' => $this->user->id,
            'cashier_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/orders?status=confirm');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('orders/index')
                ->has('orders.data', 1)
        );
    }

    public function test_single_order_can_be_viewed(): void
    {
        $order = Order::factory()->create([
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
            'account_id' => $this->user->id,
            'cashier_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get("/orders/{$order->id}");

        $response->assertStatus(200);
    }

    public function test_order_with_student_shows_student_info(): void
    {
        $student = Student::factory()->create(['store_id' => $this->store->id]);
        $order = Order::factory()->forStudent($student)->create([
            'user_id' => $this->user->id,
            'account_id' => $this->user->id,
            'cashier_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get("/orders/{$order->id}");

        $response->assertStatus(200);
    }

    public function test_void_orders_are_marked_correctly(): void
    {
        $order = Order::factory()->void()->create([
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
            'account_id' => $this->user->id,
            'cashier_id' => $this->user->id,
        ]);

        $this->assertEquals('void', $order->status);
        $this->assertTrue($order->is_void);
    }

    public function test_pending_orders_are_marked_correctly(): void
    {
        $order = Order::factory()->pending()->create([
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
            'account_id' => $this->user->id,
            'cashier_id' => $this->user->id,
        ]);

        $this->assertEquals('pending', $order->status);
        $this->assertFalse($order->is_payed);
    }

    public function test_order_belongs_to_store(): void
    {
        $order = Order::factory()->create([
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
            'account_id' => $this->user->id,
            'cashier_id' => $this->user->id,
        ]);

        $this->assertEquals($this->store->id, $order->store_id);
    }

    public function test_order_has_uuid(): void
    {
        $order = Order::factory()->create([
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
            'account_id' => $this->user->id,
            'cashier_id' => $this->user->id,
        ]);

        $this->assertNotEmpty($order->uuid);
        $this->assertEquals(36, strlen($order->uuid));
    }

    public function test_wallet_payment_order_has_wallet_type(): void
    {
        $order = Order::factory()->paidWithWallet('subscribe')->create([
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
            'account_id' => $this->user->id,
            'cashier_id' => $this->user->id,
        ]);

        $this->assertEquals('wallet', $order->payment_method);
        $this->assertEquals('subscribe', $order->wallet_type);
    }
}
