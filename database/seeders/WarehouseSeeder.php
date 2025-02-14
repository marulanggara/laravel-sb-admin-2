<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('warehouses')->insert([
            [
                'product_id' => 9,
                'supplier_id' => 7,
                'unit_id' => 5,
                'quantity' => 3,
                'price' => 200000
            ],
        ]);
    }
}
