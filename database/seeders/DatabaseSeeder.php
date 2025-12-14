<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run store and permissions seeders first
        $this->call([
            StoreSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);

        // Create default head office admin user
        $headOfficeAdmin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Head Office Admin',
                'password' => 'password',
                'email_verified_at' => now(),
                'store_id' => null, // Head office - no specific store
            ]
        );
        $headOfficeAdmin->assignRole('head-office-admin');
        // Head office admin doesn't need store_user entries - they access all stores via canAccessAllStores()

        // Create cashier for Antipolo branch
        $antipoloStore = Store::where('code', 'APL')->first();
        $cashier = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test Cashier',
                'password' => 'password',
                'email_verified_at' => now(),
                'store_id' => $antipoloStore?->id,
            ]
        );
        $cashier->assignRole('cashier');

        // Attach store to cashier via pivot table (required for multi-store access)
        if ($antipoloStore) {
            $cashier->stores()->syncWithoutDetaching([$antipoloStore->id]);
        }

        // Run other seeders
        $this->call([
            MenuSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
