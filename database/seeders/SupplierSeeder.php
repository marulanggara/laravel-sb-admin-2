<?php

namespace Database\Seeders;

use DB;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('suppliers')->insert([
            [
                'name' => 'PT. ABC',
                'address' => 'Jl. ABC',
                'contact' => '1234567890',
                'pic_name' => 'John Doe',
                'created_at' => now(),
            ],
            [
                'name' => 'PT. DEF',
                'address' => 'Jl. DEF',
                'contact' => '1234567890',
                'pic_name' => 'John Doe',
                'created_at' => now(),
            ],
            [
                'name' => 'PT. GHI',
                'address' => 'Jl. GHI',
                'contact' => '1234567890',
                'pic_name' => 'John Doe',
                'created_at' => now(),
            ]
        ]);
    }
}
