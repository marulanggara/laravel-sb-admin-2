<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat role dan permission
        $roles = [
            [
                'name' => 'admin',
                'guard_name' => 'web',
            ],
            [
                'name' => 'user',
                'guard_name' => 'web',
            ]
        ];

        // Buat permission product
        $listProduct = Permission::firstOrCreate(['name' => 'list product']);
        $readProduct = Permission::firstOrCreate(['name' => 'show product']);
        $createProduct = Permission::firstOrCreate(['name' => 'create product']);
        $updateProduct = Permission::firstOrCreate(['name' => 'update product']);
        $deleteProduct = Permission::firstOrCreate(['name' => 'delete product']);

        // Buat permission supplier
        $listSupplier = Permission::firstOrCreate(['name' => 'list supplier']);
        $readSupplier = Permission::firstOrCreate(['name' => 'show supplier']);
        $createSupplier = Permission::firstOrCreate(['name' => 'create supplier']);
        $updateSupplier = Permission::firstOrCreate(['name' => 'update supplier']);
        $deleteSupplier = Permission::firstOrCreate(['name' => 'delete supplier']);

        // Buat permission payments
        $listPayment = Permission::firstOrCreate(['name' => 'list payment']);
        $readPayment = Permission::firstOrCreate(['name' => 'show payment']);
        $createPayment = Permission::firstOrCreate(['name' => 'create payment']);
        $updatePayment = Permission::firstOrCreate(['name' => 'update payment']);
        $deletePayment = Permission::firstOrCreate(['name' => 'delete payment']);

        // Buat permission warehouse
        $listWarehouse = Permission::firstOrCreate(['name' => 'list warehouse']);
        $readWarehouse = Permission::firstOrCreate(['name' => 'show warehouse']);
        $createWarehouse = Permission::firstOrCreate(['name' => 'create warehouse']);
        $updateWarehouse = Permission::firstOrCreate(['name' => 'update warehouse']);
        $deleteWarehouse = Permission::firstOrCreate(['name' => 'delete warehouse']);

        // Tambah permission ke role
        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate($roleData);

            // Tambah permission ke role admin
            if ($role->name == 'admin') {
                $role->givePermissionTo([
                    $listProduct, $readProduct, $createProduct, $updateProduct, $deleteProduct,
                    $listSupplier, $readSupplier, $createSupplier, $updateSupplier, $deleteSupplier,
                    $listPayment, $readPayment, $createPayment, $updatePayment, $deletePayment,
                    $listWarehouse, $readWarehouse, $createWarehouse, $updateWarehouse, $deleteWarehouse
                ]);
            }
            // Tambah permission ke role user
            if ($role->name == 'user') {
                $role->givePermissionTo([
                    $listProduct, $readProduct,
                    $listSupplier, $readSupplier,
                    $listPayment, $readPayment
                ]);
            }
        }
    }
}
