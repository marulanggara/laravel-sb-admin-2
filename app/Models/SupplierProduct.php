<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SupplierProduct extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'supplier_product';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'supplier_id',
        'product_id',
        'price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Fungsi Ambil data product yang terkait dengan supplier
    public static function getProduct($supplier_id)
    {
        return DB::table('supplier_product')
            ->leftJoin('products', 'supplier_product.product_id', '=', 'products.id')
            ->leftJoin('suppliers', 'supplier_product.supplier_id', '=', 'suppliers.id')
            ->leftJoin('units', 'products.unit_id', '=', 'units.id')
            ->where('supplier_product.supplier_id', $supplier_id)
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.code as product_code',
                'supplier_product.price',
                'units.name as unit_name',
                'products.unit_id',
            )
            ->whereNull('supplier_product.deleted_at')
            ->whereNull('suppliers.deleted_at')
            ->whereNull('products.deleted_at')
            ->whereNull('units.deleted_at')
            ->orderBy('products.name', 'asc')
            ->get();
    }
}
