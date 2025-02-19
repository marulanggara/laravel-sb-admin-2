<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Log\ProductHistory;

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
        // Simpan data produk menggunakan Eloquent
        $newProduct = Product::create([
            'name' => $data['name'],
            'code' => $data['code'],
            'unit_id' => $data['unit_id'],
            'created_at' => now(),
            'updated_at' => null,
        ]);

        // Simpan log product
        $logData = [
            'user_id' => auth()->user()->id,
            'product_id' => $newProduct->id,
            'action' => 'create',
            'old_data' => json_encode([]),
            'new_data' => json_encode($newProduct),
            'created_at' => now(),
            'updated_at' => null,
        ];
        DB::table('log_histories.product_log_histories')->insert($logData);

        return $newProduct;
    }

    // Mengupdate data product dengan query builder
    public static function updateProduct($data, $id)
    {
        // Cari product berdasarkan id
        $product = DB::table('products')->where('id', $id)->first();

        if (!$product) {
            return false;
        }

        // Simpan perubahan data sebelum update (old_data)
        $old_data = (array) $product; // Mengambil data sebelum update

        // Update produk dengan query builder
        $updated = DB::table('products')
            ->where('id', $id)
            ->update([
                'name' => $data['name'],
                'unit_id' => $data['unit_id'],
                'updated_at' => now(),
            ]);

        if($updated > 0){
            // Simpan data terbaru dari product setelah update (new_data)
            $new_data = DB::table('products')->where('id', $id)->first();
            $new_data = (array) $new_data; // Mengambil data terbaru

            // Simpan perubahan data ke log
            ProductHistory::create([
                'user_id' => auth()->user()->id,
                'product_id' => $id,
                'action' => 'update',
                'old_data' => json_encode($old_data),
                'new_data' => json_encode($new_data),
                'created_at' => now(),
            ]);

            return true;
        }
        return false;
    }

    // Menghapus data product dengan query builder
    public static function deleteProduct($id)
    {
        // Ambil product berdasarkan id
        $product = DB::table('products')->where('id', $id)->first();

        // Jika ada product
        if ($product) {
            /// Simpan log
            $logData = [
                'user_id' => auth()->user()->id,
                'product_id' => $id,
                'action' => 'delete',
                'old_data' => json_encode($product),
                'new_data' => json_encode([]),
            ];
            DB::table('log_histories.product_log_histories')->insert($logData);

            // Hapus product
            DB::table('products')->where('id', $id)->update([
                'deleted_at' => now(),
            ]);

            return true;
        }
        return false;
    }

    // Search product by name and code
    public static function searchProduct($search)
    {
        // Search product by name and code no case sensitive
        return Product::where('name', 'ILIKE', '%'.$search.'%')
                    ->orWhere('code', 'ILIKE', '%'.$search.'%')
                    ->paginate(25);
    }

    // Search Product from warehouse
    public static function searchProductWarehouse($search)
    {
        $products = self::whereRaw('LOWER(products.name) LIKE ?', ['%' . strtolower($search) . '%'])
            ->whereHas('warehouses', function ($query) {
                $query->where('quantity', '>', 0);
            })
            ->limit(10)
            ->get();

        return $products->map(function ($product) {
            // Mengambil warehouse dengan stock tersedia dengan FIFO
            $warehouse = $product->warehouses()
                ->where('quantity', '>', 0)
                ->orderBy('created_at', 'asc')
                ->first();

            if (!$warehouse) {
                return null;
            }

            return [
                'id' => $product->id,
                'text' => $product->name,
                'available_stock' => $warehouse->quantity,
                'warehouse_id' => $warehouse->id,
                'product_code' => $warehouse->product->code,
                'unit_name' => $warehouse->unit->name,
                'selling_price' => $warehouse->selling_price
            ];
        })->filter()->sortBy('text')->values();
    }
}
