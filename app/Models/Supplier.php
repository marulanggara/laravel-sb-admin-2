<?php

namespace App\Models;

use App\Models\Log\SupplierHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use DB;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'suppliers';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'address',
        'contact',
        'pic_name',
        'created_at',
        'updated_at',
    ];

    // Relasi product ke tabel supplier_product melalui pivot
    public function products()
    {
        return $this->belongsToMany(Product::class, 'supplier_product')
                    ->withPivot('price')
                    ->withTimestamps()
                    ->with('unit');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($supplier) {
            SupplierProduct::where('supplier_id', $supplier->id)->update(['deleted_at' => now()]);
        });
    }

    // Menampilkan data supplier dan product dengan query builder
    public static function getAllSuppliersWithProducts($perPage)
    {
        return DB::table('suppliers')
                    ->leftJoin('supplier_product', 'suppliers.id', '=', 'supplier_product.supplier_id')
                    ->leftJoin('products', 'supplier_product.product_id', '=', 'products.id')
                    ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                    ->select(
                        'suppliers.*',
                        DB::raw("STRING_AGG(products.name, ', ') as product_names"),
                        DB::raw("STRING_AGG(units.name, ', ') as unit_names")
                    )
                    ->whereNull('suppliers.deleted_at')
                    ->groupBy('suppliers.id')
                    ->orderBy('suppliers.name', 'asc')
                    ->paginate($perPage);
    }

    // Menambah data supplier dengan query builder
    public static function addSupplier($data)
    {
        // insert data supplier dan dapatkan id supplier
        $supplierId = DB::table('suppliers')->insertGetId([
            'name' => $data['name'],
            'address' => $data['address'],
            'contact' => $data['contact'],
            'pic_name' => $data['pic_name'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Cek ada produk yang dikirim sebelum dimasukkan ke supplier_product
        if(isset($data['products']) && is_array($data['products'])) {
            $supplierProducts = [];

            // Looping product yang dikirim
            foreach ($data['products'] as $productId => $productData) {
                // Cek data price tidak kosong
                if(!empty($productData['price'])) {
                    $supplierProducts[] = [
                        'supplier_id' => $supplierId,
                        'product_id' => $productId,
                        'price' => $productData['price'],
                    ];
                }
            }

            // Jika ada produk yang valid, masukkan ke dalam supplier_product
            if(!empty($supplierProducts)) {
                DB::table('supplier_product')->insert($supplierProducts);
            }
        }
        return $supplierId;
    }

    // Mengambil data supplier berdasarkan id
    public static function getSupplierById($id)
    {
        $supplier = DB::table('suppliers')
            ->leftJoin('supplier_product', 'suppliers.id', '=', 'supplier_product.supplier_id')
            ->leftJoin('products', 'supplier_product.product_id', '=', 'products.id')
            ->leftJoin('units', 'products.unit_id', '=', 'units.id')
            ->select(
                'suppliers.id',
                'suppliers.name',
                'suppliers.address',
                'suppliers.contact',
                'suppliers.pic_name',
                DB::raw("COALESCE(STRING_AGG(products.name, ', '), '') as product_names"),
                DB::raw("COALESCE(STRING_AGG(units.name, ', '), '') as unit_names")
            )

            ->whereNull('suppliers.deleted_at')
            ->where('suppliers.id', $id)
            ->groupBy(
                'suppliers.id',
                'suppliers.name',
                'suppliers.address',
                'suppliers.contact',
                'suppliers.pic_name'
            )
            ->orderBy('suppliers.name', 'asc')
            ->first();

        return $supplier;
    }


    // Mengupdate data supplier dengan query builder
    public static function updateSupplier($id, $data)
    {
        // Ambil supplier berdasarkan id
        $supplier = DB::table('suppliers')->where('id', $id)->first();

        // Jika supplier tidak ditermukan, return false
        if (!$supplier) {
            return false;
        }

        // Simpan data sebelum perubahan
        $oldData = [
            'name' => $supplier->name,
            'address' => $supplier->address,
            'contact' => $supplier->contact,
            'pic_name' => $supplier->pic_name,
        ];

        // Update supplier tanpa mengubah code supplier
        DB::table('suppliers')
            ->where('id', $id)
            ->update([
                'name' => $data['name'],
                'address' => $data['address'],
                'contact' => $data['contact'],
                'pic_name' => $data['pic_name'],
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        // Ambil data setelah update
        $newData = DB::table('suppliers')->where('id', $id)->first();

        // Simpan log perubahan
        SupplierHistory::create([
            'supplier_id' => $id,
            'user_id' => Auth::user()->id,
            'action' => 'update',
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($newData),
        ]);
        
        // Jika ada product yang dikirim, update relasi supplier_product
        if (isset($data['products']) && is_array($data['products'])) {
            // Simpan data product lama
            $oldProducts = DB::table('supplier_product')
                        ->where('supplier_id', $id)
                        ->get()
                        ->map(function ($product) {
                            return [
                                'product_id' => $product->product_id,
                                'price' => $product->price];
                        })
                        ->toArray();
            // Hapus product lama dan update product baru
            DB::table('supplier_product')->where('supplier_id', $id)->delete();

            // Tambahkan product baru
            foreach ($data['products'] as $productId => $productData) {
                if (!empty($productData['price'])) {
                    DB::table('supplier_product')->insert([
                        'supplier_id' => $id,
                        'product_id' => $productId,
                        'price' => $productData['price'],
                    ]);
                }
            }

            // Simpan data product setelah update
            $newProducts = DB::table('supplier_product')
                        ->where('supplier_id', $id)
                        ->get()
                        ->map(function ($product) {
                            return [
                                'product_id' => $product->product_id,
                                'price' => $product->price];
                        })
                        ->toArray();

            // Simpan log perubahan
            SupplierHistory::create([
                'supplier_id' => $id,
                'user_id' => Auth::user()->id,
                'action' => 'update',
                'old_data' => json_encode($oldProducts),
                'new_data' => json_encode($newProducts),
            ]);
        }

        return true;
    }

    // Menghapus data supplier dengan query builder
    public static function deleteSupplier($id)
    {
        // Ambil supplier berdasarkan id
        $supplier = Supplier::find($id);

        // Jika supplier tidak ditermukan, return false
        if (!$supplier) {
            return false;
        }

        // Hapus supplier
        $supplier->delete();
        return true;
    }

    // Search supplier by name and pic name
    public static function searchSupplier($search)
    {
        return DB::table('suppliers')
                    ->leftJoin('supplier_product', 'suppliers.id', '=', 'supplier_product.supplier_id')
                    ->leftJoin('products', 'supplier_product.product_id', '=', 'products.id')
                    ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                    ->select('suppliers.*', 'supplier_product.price as product_price', 'products.name as product_name', 'units.name as unit_name')
                    ->where(function ($query) use ($search) {
                        $query->whereRaw('LOWER(suppliers.name) LIKE ?', ['%'.strtolower($search).'%'])
                        ->orWhereRaw('LOWER(suppliers.pic_name) LIKE ?', ['%'.strtolower($search).'%']);
                    })
                    ->whereNull('suppliers.deleted_at')
                    ->orderBy('suppliers.name', 'asc')
                    ->paginate(25);
    }
}
