<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'name' => 'Baut',
                'code' => 'P001',
                'unit_id' => 1
            ],
            [
                'name' => 'Mur',
                'code' => 'P002',
                'unit_id' => 1
            ],
            [
                'name' => 'Paku',
                'code' => 'P003',
                'unit_id' => 1
            ],
            [
                'name' => 'Kayu',
                'code' => 'P004',
                'unit_id' => 5
            ],
        ]);
    }
}
