<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Store;
use App\Models\Student;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StudentFeatureTest extends TestCase
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

    public function test_students_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get('/students');

        $response->assertStatus(200);
    }

    public function test_students_are_listed_correctly(): void
    {
        Student::factory()->count(3)->create(['store_id' => $this->store->id]);

        $response = $this->actingAs($this->user)->get('/students');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('students/index')
                ->has('students.data', 3)
        );
    }

    public function test_students_can_be_filtered_by_search(): void
    {
        Student::factory()->create([
            'store_id' => $this->store->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        Student::factory()->create([
            'store_id' => $this->store->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);

        $response = $this->actingAs($this->user)->get('/students?search=John');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('students/index')
                ->has('students.data', 1)
        );
    }

    public function test_students_can_be_filtered_by_status(): void
    {
        Student::factory()->create(['store_id' => $this->store->id, 'is_active' => true]);
        Student::factory()->inactive()->create(['store_id' => $this->store->id]);

        $response = $this->actingAs($this->user)->get('/students?status=active');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('students/index')
                ->has('students.data', 1)
        );
    }

    public function test_student_can_be_created(): void
    {
        $studentData = [
            'student_id' => '2024-0001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'test@example.com',
            'wallet_type' => 'subscribe',
        ];

        $response = $this->actingAs($this->user)->post('/students', $studentData);

        $response->assertRedirect();
        $this->assertDatabaseHas('students', [
            'student_id' => '2024-0001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'store_id' => $this->store->id,
        ]);
    }

    public function test_student_can_be_updated(): void
    {
        $student = Student::factory()->create(['store_id' => $this->store->id]);

        $response = $this->actingAs($this->user)->put("/students/{$student->id}", [
            'student_id' => $student->student_id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
        ]);
    }

    public function test_student_can_be_deleted(): void
    {
        $student = Student::factory()->create(['store_id' => $this->store->id]);

        $response = $this->actingAs($this->user)->delete("/students/{$student->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    public function test_student_validation_requires_student_id(): void
    {
        $response = $this->actingAs($this->user)->post('/students', [
            'first_name' => 'Test',
            'last_name' => 'Student',
        ]);

        $response->assertSessionHasErrors('student_id');
    }

    public function test_student_validation_requires_first_name(): void
    {
        $response = $this->actingAs($this->user)->post('/students', [
            'student_id' => '2024-0001',
            'last_name' => 'Student',
        ]);

        $response->assertSessionHasErrors('first_name');
    }

    public function test_student_validation_requires_unique_student_id(): void
    {
        Student::factory()->create([
            'store_id' => $this->store->id,
            'student_id' => '2024-0001',
        ]);

        $response = $this->actingAs($this->user)->post('/students', [
            'student_id' => '2024-0001',
            'first_name' => 'Test',
            'last_name' => 'Student',
        ]);

        $response->assertSessionHasErrors('student_id');
    }

    public function test_student_search_api_returns_correct_results(): void
    {
        Student::factory()->create([
            'store_id' => $this->store->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $response = $this->actingAs($this->user)->get('/students/search?q=John');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'students' => [
                '*' => [
                    'id',
                    'student_id',
                    'full_name',
                ],
            ],
        ]);
    }

    public function test_student_can_receive_deposit(): void
    {
        $student = Student::factory()->withSubscribeWallet()->create([
            'store_id' => $this->store->id,
        ]);

        // Create the wallet first
        $student->createWallet(['name' => 'Subscribe Wallet', 'slug' => 'subscribe']);

        $response = $this->actingAs($this->user)->post("/students/{$student->id}/deposit", [
            'amount' => 100,
            'description' => 'Test deposit',
        ]);

        $response->assertRedirect();
    }

    public function test_student_can_withdraw_from_wallet(): void
    {
        $student = Student::factory()->withSubscribeWallet()->create([
            'store_id' => $this->store->id,
        ]);

        // Create and fund the wallet
        $wallet = $student->createWallet(['name' => 'Subscribe Wallet', 'slug' => 'subscribe']);
        $student->deposit(500);

        $response = $this->actingAs($this->user)->post("/students/{$student->id}/withdraw", [
            'amount' => 100,
            'description' => 'Test withdrawal',
        ]);

        $response->assertRedirect();
    }

    public function test_deposit_requires_wallet_type(): void
    {
        $student = Student::factory()->withoutWallet()->create([
            'store_id' => $this->store->id,
        ]);

        $response = $this->actingAs($this->user)->post("/students/{$student->id}/deposit", [
            'amount' => 100,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash.type', 'error');
    }

    public function test_student_full_name_accessor_works(): void
    {
        $student = Student::factory()->create([
            'store_id' => $this->store->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $student->full_name);
    }

    public function test_student_qr_code_url_is_generated(): void
    {
        $student = Student::factory()->create(['store_id' => $this->store->id]);

        $this->assertNotEmpty($student->qr_code_url);
        $this->assertStringContainsString('qr-code', $student->qr_code_url);
    }
}
