<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('supplier_product')->insert([
            [
                'supplier_id' => 1,
                'product_id' => 1,
                'price' => 10000,
                'created_at' => now(),
            ],
            [
                'supplier_id' => 1,
                'product_id' => 2,
                'price' => 20000,
                'created_at' => now(),
            ],
            [
                'supplier_id' => 2,
                'product_id' => 3,
                'price' => 30000,
                'created_at' => now(),
            ]
        ]);
    }
}
