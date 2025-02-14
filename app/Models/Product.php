<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'products';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'name',
        'code',
        'unit_id',
        'created_at',
        'updated_at',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    
    // Relasi product ke tabel supplier_product melalui pivot
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'supplier_product')
        ->withPivot('price')
        ->withTimestamps(); // Ambil data harga dari pivot table
    }

    // Relasi product ke tabel warehouse
    public function warehouses()
    {
        return $this->hasOne(Warehouse::class, 'product_id', 'id');
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class, 'product_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($product) {
            SupplierProduct::where('product_id', $product->id)->update(['deleted_at' => now()]);
        });
    }

    // Fungsi untuk membuat kode unik (2 huruf dan 3 angka)
    public static function generateUniqueCode()
    {
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 2) . str_pad(mt_rand(100, 999), 3, '0', STR_PAD_LEFT));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    // Menampilkan data product dan unit
    public static function getAllProductsWithUnits($perPage)
    {
        return Product::with('unit')
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    // Mengambil data product berdasarkan id
    public static function getProductById($id)
    {
        return DB::table('products')
                    ->join('units', 'products.unit_id', '=', 'units.id')
                    ->select('products.*', 'units.name as unit_name')
                    ->whereNull('products.deleted_at')
                    ->where('products.id', $id)
                    ->first();
    }

    // Menambah data product dengan query builder
    public static function addProduct($data)
    {
        return DB::table('products')->insert([
            'name' => $data['name'],
            'code' => $data['code'],
            'unit_id' => $data['unit_id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // Mengupdate data product dengan query builder
    public static function updateProduct($data, $id)
    {

        // Update produk dengan query builder
        $updated = DB::table('products')
            ->where('id', $id)
            ->update([
                'name' => $data['name'],
                'unit_id' => $data['unit_id'],
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return $updated > 0;
    }

    // Menghapus data product dengan query builder
    public static function deleteProduct($id)
    {
        // Ambil product berdasarkan id
        $product = DB::table('products')->where('id', $id)->first();

        // Jika ada product
        if ($product) {
            // Hapus product
            // DB::table('products')->where('id', $id)->delete();
            DB::table('products')->where('id', $id)->update([
                'deleted_at' => date('Y-m-d H:i:s'),
            ]);

            return true;
        }
        return false;
    }

    // Search product by name and code
    public static function searchProduct($search)
    {
        // Search product by name and code no case sensitive
        return Product::whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($search).'%'])
                    ->orWhereRaw('LOWER(code) LIKE ?', ['%'.strtolower($search).'%'])
                    ->paginate(25);
    }
}
