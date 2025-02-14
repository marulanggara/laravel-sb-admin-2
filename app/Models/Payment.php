<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;

class Payment extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'payments';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'supplier_id',
        'supplier_name',
        'supplier_address',
        'supplier_contact',
        'supplier_pic_name',
        'total_price',
        'is_received',
        'status',
    ];

    // Relasi payment dengan tabel supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Relasi payment dengan tabel payment_item
    public function items()
    {
        return $this->hasMany(PaymentItem::class, 'payment_id', 'id');
    }

    // Ambil semua data pembayaran dengan query builder
    public static function getAllPayment($perPage)
    {
        return DB::table('payments')
            ->leftJoin('suppliers', 'payments.supplier_id', '=', 'suppliers.id')
            ->leftJoin('payment_items', 'payments.id', '=', 'payment_items.payment_id')
            ->leftJoin('products', 'payment_items.product_id', '=', 'products.id')
            ->select(
                'payments.id',
                'payments.supplier_id',
                'suppliers.name as supplier_name',
                'suppliers.address as supplier_address',
                'suppliers.contact as supplier_contact',
                'suppliers.pic_name as supplier_pic_name',
                'payments.total_price',
                'payments.is_received',
                'payments.status',
                'payments.created_at', // Pastikan ini ada
                DB::raw("COALESCE(SUM(payment_items.quantity), 0) as total_quantity") // Perbaiki SUM
            )
            ->groupBy(
                'payments.id',
                'payments.supplier_id',
                'suppliers.name',
                'suppliers.address',
                'suppliers.contact',
                'suppliers.pic_name',
                'payments.total_price',
                'payments.is_received',
                'payments.status',
                'payments.created_at'
            )
            ->whereNull('payments.deleted_at')
            ->whereNull('payment_items.deleted_at')
            ->whereNull('products.deleted_at')
            ->whereNull('suppliers.deleted_at')
            ->orderBy('payments.created_at', 'desc')
            ->paginate($perPage);
    }


    // Search payment name and status
    public static function searchPayment($search)
    {
        return DB::table('payments')
            ->leftJoin('suppliers', 'payments.supplier_id', '=', 'suppliers.id')
            ->leftJoin('payment_items', 'payments.id', '=', 'payment_items.payment_id')
            ->leftJoin('products', 'payment_items.product_id', '=', 'products.id')
            ->select(
                'payments.id',
                'payments.supplier_id',
                'suppliers.name as supplier_name',
                'suppliers.address as supplier_address',
                'suppliers.contact as supplier_contact',
                'suppliers.pic_name as supplier_pic_name',
                'payments.total_price',
                'payments.is_received',
                'payments.status',
                'payments.created_at',
                DB::raw("SUM(payment_items.quantity) as total_quantity"),
            )
            ->whereNull('payments.deleted_at')
            ->whereNull('payment_items.deleted_at')
            ->whereNull('products.deleted_at')
            ->whereNull('suppliers.deleted_at')
            ->where(function ($query) use ($search) {
                $search = strtolower($search); // Ubah ke lowercase sebelum digunakan
                $query->whereRaw('LOWER(payments.supplier_name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(payments.status) LIKE ?', ["%{$search}%"]);
            })
            ->groupBy(
                'payments.id',
                'suppliers.name',
                'suppliers.address',
                'suppliers.contact',
                'suppliers.pic_name',
                'payments.total_price',
                'payments.is_received',
                'payments.status',
                'payments.created_at'
            )
            ->orderBy('payments.created_at', 'desc')
            ->paginate(25);
    }

    // Menambah data payment dengan query builder
    public static function addPayment($data)
    {
        return DB::transaction(function () use ($data) {
            // Ambil nama supplier dari database
            $supplier = DB::table('suppliers')->where('id', $data['supplier_id'])->first();
            // Hitung total harga pembayaran berdasarkan jumlah item dan harga tidap product
            $total_price = collect($data['items'])->sum(fn ($item) => $item['quantity'] * $item['price']);
            
            // Simpan payment kedalam database payments
            $paymentId = DB::table('payments')->insertGetId([
                'supplier_id' => $data['supplier_id'],
                'supplier_name' => $supplier->name,
                'supplier_address' => $supplier->address,
                'supplier_contact' => $supplier->contact,
                'supplier_pic_name' => $supplier->pic_name,
                'total_price' => $total_price,
                'created_at' => now(),
            ]);
    
            // Jika gagal, kembalikan false
            if(!$paymentId) {
                return false;
            }
    
            // Buat array untuk menyimpan banyak data sekaligus
            $paymentItems = [];
            foreach($data['items'] as $item) {
                // Tambahkan data item ke dalam array
                $paymentItems[] = [
                    'payment_id' => $paymentId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_code' => $item['product_code'],
                    'unit_id' => $item['unit_id'],
                    'unit_name' => $item['unit_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'created_at' => now(),
                ];
            }
    
            // Insert data payment_items
            DB::table('payment_items')->insert($paymentItems);
            return $paymentId;
        });
    }

    // Mengambil data payment berdasarkan id
    public static function getPaymentById($id)
    {
        // Ambil informasi pembayaran beserta supplier-nya
        $payment = DB::table('payments')
            ->leftJoin('suppliers', 'payments.supplier_id', '=', 'suppliers.id')
            ->select(
                'payments.id as payment_id',
                'payments.supplier_id',
                'payments.status',
                'payments.is_received',
                'suppliers.name as supplier_name',
            )
            ->whereNull('payments.deleted_at')
            ->whereNull('suppliers.deleted_at')
            ->where('payments.id', $id)
            ->first();

        // Jika data pembayaran ditemukan
        if ($payment) {
            // Ambil item-item yang terkait dengan pembayaran tersebut
            $payment->items = DB::table('payment_items')
                ->leftJoin('products', 'payment_items.product_id', '=', 'products.id')
                ->leftJoin('units', 'payment_items.unit_id', '=', 'units.id')
                ->where('payment_items.payment_id', $payment->payment_id)
                ->select(
                    'payment_items.quantity',
                    'payment_items.price',
                    'products.name as product_name',
                    'units.name as unit_name',
                )
                ->whereNull('payment_items.deleted_at')
                ->whereNull('products.deleted_at')
                ->whereNull('units.deleted_at')
                ->get();

            // Hitung total quantity dan total price dari items
            $payment->total_quantity = $payment->items->sum('quantity');
            $payment->total_price = $payment->items->sum(function ($item) {
                return $item->quantity * $item->price;
            });
        }

        return $payment;
    }
}
