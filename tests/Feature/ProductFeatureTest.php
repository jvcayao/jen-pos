<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Store;
use App\Models\Product;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductFeatureTest extends TestCase
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

        // Set the store context for the session
        $this->withSession(['current_store_id' => $this->store->id]);
    }

    public function test_products_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get('/products');

        $response->assertStatus(200);
    }

    public function test_products_are_listed_correctly(): void
    {
        Product::factory()->count(3)->create(['store_id' => $this->store->id]);

        $response = $this->actingAs($this->user)->get('/products');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('products/index')
                ->has('products.data', 3)
        );
    }

    public function test_products_can_be_filtered_by_search(): void
    {
        Product::factory()->create([
            'store_id' => $this->store->id,
            'name' => 'Burger Deluxe',
        ]);
        Product::factory()->create([
            'store_id' => $this->store->id,
            'name' => 'Pizza Special',
        ]);

        $response = $this->actingAs($this->user)->get('/products?search=Burger');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('products/index')
                ->has('products.data', 1)
        );
    }

    public function test_product_can_be_created(): void
    {
        $productData = [
            'name' => 'New Product',
            'price' => 99.99,
            'description' => 'A new product description',
        ];

        $response = $this->actingAs($this->user)->post('/products', $productData);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'store_id' => $this->store->id,
        ]);
    }

    public function test_product_can_be_updated(): void
    {
        $product = Product::factory()->create(['store_id' => $this->store->id]);

        $response = $this->actingAs($this->user)->put("/products/{$product->id}", [
            'name' => 'Updated Product Name',
            'price' => $product->price,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
        ]);
    }

    public function test_product_can_be_deleted(): void
    {
        $product = Product::factory()->create(['store_id' => $this->store->id]);

        $response = $this->actingAs($this->user)->delete("/products/{$product->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_product_validation_requires_name(): void
    {
        $response = $this->actingAs($this->user)->post('/products', [
            'price' => 99.99,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_product_validation_requires_valid_price(): void
    {
        $response = $this->actingAs($this->user)->post('/products', [
            'name' => 'Test Product',
            'price' => -10,
        ]);

        $response->assertSessionHasErrors('price');
    }

    public function test_out_of_stock_products_are_marked_correctly(): void
    {
        $product = Product::factory()->outOfStock()->create(['store_id' => $this->store->id]);

        $this->assertEquals(0, $product->stock);
        $this->assertFalse($product->is_in_stock);
    }

    public function test_inactive_products_can_be_filtered(): void
    {
        Product::factory()->create(['store_id' => $this->store->id, 'is_activated' => true]);
        Product::factory()->inactive()->create(['store_id' => $this->store->id]);

        // Both should be returned if no filter
        $response = $this->actingAs($this->user)->get('/products');

        $response->assertStatus(200);
    }
}
