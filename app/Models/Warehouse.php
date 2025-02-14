<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'warehouses';

    protected $dates = ['deleted_at'];
    protected $fillable = [
        'payment_id',
        'product_id',
        'supplier_id',
        'unit_id',
        'quantity',
        'price',
        'selling_price',
        'created_at',
        'updated_at',
    ];

    // Relasi dengan tabel products
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    // Relasi dengan tabel suppliers
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Relasi dengan tabel units
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    // Method untuk menghitung total stok
    public function totalStock($quantity)
    {
        return $this->quantity >= $quantity;
    }

    // Ambil data warehouse dengan query builder
    public static function getAllWarehouse($perPage)
    {
        return DB::table('warehouses')
            ->leftJoin('products', 'warehouses.product_id', '=', 'products.id')
            ->leftJoin('units', 'warehouses.unit_id', '=', 'units.id')
            ->leftJoin('suppliers', 'warehouses.supplier_id', '=', 'suppliers.id')
            ->select(
                'warehouses.product_id',
                'warehouses.selling_price',
                DB::raw("MAX(products.name) as product_name"),
                DB::raw("MAX(suppliers.name) as supplier_name"),
                DB::raw("MAX(units.name) as unit_name"),
                DB::raw("SUM(warehouses.quantity) as total_quantity")
            )
            ->whereNull('warehouses.deleted_at')
            ->whereNull('products.deleted_at')
            ->whereNull('suppliers.deleted_at')
            ->whereNull('units.deleted_at')
            ->where('warehouses.selling_price', '>=', 0)
            ->groupBy('warehouses.product_id', 'warehouses.selling_price')
            ->orderBy(DB::raw("MAX(products.name)"), 'asc')
            ->paginate($perPage);
    }

    // Search warehouse name
    public static function searchWarehouse($search)
    {
        return DB::table('warehouses')
        ->leftJoin('products', 'warehouses.product_id', '=', 'products.id')
        ->leftJoin('units', 'warehouses.unit_id', '=', 'units.id')
        ->leftJoin('suppliers', 'warehouses.supplier_id', '=', 'suppliers.id')
        ->select(
            'warehouses.product_id',
            'warehouses.selling_price',
            DB::raw("MAX(products.name) as product_name"),
            DB::raw("MAX(suppliers.name) as supplier_name"),
            DB::raw("MAX(units.name) as unit_name"),
            DB::raw("SUM(warehouses.quantity) as total_quantity")
        )
        ->whereNull('warehouses.deleted_at')
        ->whereNull('products.deleted_at')
        ->whereNull('suppliers.deleted_at')
        ->whereNull('units.deleted_at')
        ->where('warehouses.selling_price', '>=', 0)
        ->whereRaw('LOWER(products.name) LIKE ?', ["%{$search}%"])
        ->groupBy('warehouses.product_id', 'warehouses.selling_price')
        ->paginate(25);
    }

    // Ambil data warehouse berdasarkan id dengan query builder
    public static function getWarehouseById($product_id, $perPage)
    {
        return DB::table('warehouses')
                    ->leftJoin('products', 'warehouses.product_id', '=', 'products.id')
                    ->leftJoin('suppliers', 'warehouses.supplier_id', '=', 'suppliers.id')
                    ->leftJoin('units', 'warehouses.unit_id', '=', 'units.id')
                    ->select(
                        'warehouses.*',
                        'products.name as product_name',
                        'suppliers.name as supplier_name',
                        'units.name as unit_name',
                        DB::raw("SUM(warehouses.quantity) as total_quantity")
                    )
                    ->whereNull('warehouses.deleted_at')
                    ->whereNull('products.deleted_at')
                    ->whereNull('suppliers.deleted_at')
                    ->whereNull('units.deleted_at')
                    ->where('warehouses.product_id', $product_id,)
                    ->groupBy('warehouses.id', 'products.name', 'suppliers.name', 'units.name')
                    ->orderBy('warehouses.created_at', 'asc')
                    ->paginate($perPage);
    }

    // Update kolom selling price
    public static function updateSellingPrice($product_id, $selling_price)
    {
        return DB::table('warehouses')
                    ->where('product_id', $product_id)
                    ->update([
                        'selling_price' => $selling_price,
                        'updated_at' => now(),
                    ]);
    }
}
