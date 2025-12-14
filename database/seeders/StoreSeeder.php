<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            [
                'name' => 'Antipolo Branch',
                'slug' => 'antipolo-branch',
                'code' => 'APL',
                'address' => 'Antipolo City, Rizal',
                'phone' => null,
                'email' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Iloilo Branch',
                'slug' => 'iloilo-branch',
                'code' => 'ILO',
                'address' => 'Iloilo City, Iloilo',
                'phone' => null,
                'email' => null,
                'is_active' => true,
            ],
        ];

        foreach ($stores as $store) {
            Store::firstOrCreate(
                ['code' => $store['code']],
                $store
            );
        }
    }
}
