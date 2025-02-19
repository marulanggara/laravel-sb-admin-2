<?php

namespace App\Models;

use App\Models\Log\PaymentHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;
use Request;

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
                'payments.total_price',
                'payments.is_received',
                'payments.status',
                'payments.created_at', // Pastikan ini ada
                'suppliers.name as supplier_name',
                'suppliers.address as supplier_address',
                'suppliers.contact as supplier_contact',
                'suppliers.pic_name as supplier_pic_name',
                DB::raw("COALESCE(SUM(payment_items.quantity), 0) as total_quantity") // Perbaiki SUM
            )
            ->groupBy(
                'payments.id',
                'payments.supplier_id',
                'payments.total_price',
                'payments.is_received',
                'payments.status',
                'payments.created_at',
                'suppliers.name',
                'suppliers.address',
                'suppliers.contact',
                'suppliers.pic_name',
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
                'payments.total_price',
                'payments.is_received',
                'payments.status',
                'payments.created_at',
                'suppliers.name as supplier_name',
                'suppliers.address as supplier_address',
                'suppliers.contact as supplier_contact',
                'suppliers.pic_name as supplier_pic_name',
                DB::raw("SUM(payment_items.quantity) as total_quantity"),
            )
            ->whereNull('payments.deleted_at')
            ->whereNull('payment_items.deleted_at')
            ->whereNull('products.deleted_at')
            ->whereNull('suppliers.deleted_at')
            ->where(function ($query) use ($search) {
                $search = strtolower($search); // Ubah ke lowercase sebelum digunakan
                $query->where('payments.supplier_name', 'ILIKE', "%{$search}%")
                    ->orWhere('payments.status', 'ILIKE', "%{$search}%");
            })
            ->groupBy(
                'payments.id',
                'payments.total_price',
                'payments.is_received',
                'payments.status',
                'payments.created_at',
                'suppliers.name',
                'suppliers.address',
                'suppliers.contact',
                'suppliers.pic_name',
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

            // Jika supplier tidak ditemukan, kembalikan false
            if (!$supplier) {
                return false;
            }
            $items = array_filter($data['items'], function($item) {
                return isset($item['product_id'], $item['quantity'], $item['price']) && $item['quantity'] > 0;
            });
            // Jika tidak ada item yang valid, kembalikan false
            if (empty($items)) {
                return false;
            }
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
                'created_by' => auth()->user()->name,
                'created_at' => now(),
            ]);
    
            // Jika gagal, kembalikan false
            if(!$paymentId) {
                return false;
            }
    
            // Buat array untuk menyimpan banyak data sekaligus
            $paymentItems = [];
            foreach($items as $item) {
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
            // Ambil payment yang baru ditambahkan
            return DB::table('payments')->where('id', $paymentId)->first();
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

    // Update status payment
    public static function updateStatus($id, $data)
    {
        // Ambil data payment berdasarkan id
        $payment = self::findOrFail($id);

        // Simpan data lama sebelum update
        $oldStatus = $payment->status;
        $oldReceived = $payment->is_received;

        // Update status payment
        $payment->status = $data['status'];
        $payment->is_received = $data['is_received'] ?? $payment->is_received;
        $payment->save();

        // Simpan log untuk status perubahan
        PaymentHistory::create([
            'user_id' => auth()->user()->id,
            'payment_id' => $payment->id,
            'action' => 'update',
            'old_data' => json_encode([
                'id' => $payment->id,
                'status' => $oldStatus,
                'is_received' => $oldReceived,
            ]),
            'new_data' => json_encode([
                'id' => $payment->id,
                'status' => $payment->status,
                'is_received' => $payment->is_received,
            ]),
        ]);

        // Jika status payment adalah 'lunas', update quantity produk di warehouse
        if ($payment->status == 'lunas') {
            foreach ($payment->items as $item) {
                // Ambil produk yang terkait dengan payment item
                $product = $item->product;

                if ($product) {
                    // Tambahkan quantity produk berdasarkan quantity yang ada di payment item
                    $newQuantity = $product->quantity + $item->quantity;
                    $product->quantity = $newQuantity;
                    $product->save();  // Simpan perubahan quantity ke produk

                    // Simpan log perubahan quantity dan product yang masuk
                    PaymentHistory::create([
                        'user_id' => auth()->user()->id,
                        'payment_id' => $payment->id,
                        'action' => 'update',
                        'old_data' => json_encode([
                            'id' => $payment->id,
                            'product_id' => $product->id,
                            'quantity' => $product->quantity - $item->quantity,
                        ]),
                        'new_data' => json_encode([
                            'id' => $payment->id,
                            'product_id' => $product->id,
                            'quantity' => $product->quantity,
                        ]),
                    ]);
                }
            }
        }

        return $payment; // Return payment yang baru ditambahkan
    }

    // Process payment
    public static function processPaymentFunction($paymentId, $paymentStatus, $isReceived, $request)
    {
        // Ambil data payment berdasarkan id
        $payment = self::with('items')->findOrFail($paymentId);
        if (!$payment) {
            throw new \Exception('Payment not found');
        }

        // Simpan data lama sebelum update
        $oldStatus = $payment->status;
        $oldReceived = $payment->is_received;

        // Cek jika status pembayaran adalah 'lunas' atau 'on progress'
        if ($paymentStatus == 'lunas' || $paymentStatus == 'on progress') {
            if ($payment->is_received) {
                // Barang sudah diterima, hanya update status
                $payment->status = $paymentStatus;
            } else {
                // Jika barang belum diterima, maka update status saja
                if ($request->input('is_received', false)) {
                    // Jika is_received bernilai true, proses quantity dan update status
                    $payment->status = $paymentStatus;
                    $payment->is_received = true;

                    // Loop untuk setiap item dan update quantity di warehouse
                    foreach ($payment->items as $item) {
                        // Cek apakah barang sudah ada di warehouse
                        // $warehouse = Warehouse::where('product_id', $item->product_id)
                        //     ->where('supplier_id', $payment->supplier_id)
                        //     ->where('unit_id', $item->unit_id)
                        //     ->where('price', $item->price)
                        //     ->where('payment_id', $payment->id)
                        //     ->whereNull('deleted_at')
                        //     ->first();
                        $warehouse = Warehouse::create([
                            'payment_id' => $payment->id,
                            'supplier_id' => $payment->supplier_id,
                            'product_id' => $item->product_id,
                            'unit_id' => $item->unit_id,
                            'price' => $item->price,
                            'quantity' => $item->quantity,
                        ]);

                        // if ($warehouse) {
                        //     $oldQuantity = $warehouse->quantity;
                        //     // Jika barang sudah ada di warehouse, tambah quantity-nya
                        //     $warehouse->quantity += $item->quantity;
                        //     $warehouse->save();
                        // } else {
                        //     // Jika barang belum ada di warehouse, buat baru
                        //     $oldQuantity = 0;
                        //     Warehouse::create([
                        //         'payment_id' => $payment->id,
                        //         'supplier_id' => $payment->supplier_id,
                        //         'product_id' => $item->product_id,
                        //         'unit_id' => $item->unit_id,
                        //         'price' => $item->price,
                        //         'quantity' => $item->quantity,
                        //     ]);
                        // }

                        // Update selling_price jika product yang baru masuk sudah ada di warehouse sebelumnya
                        $existingWarehouse = Warehouse::where('product_id', $item->product_id)
                                            ->whereNotNull('selling_price')
                                            ->orderBy('created_at', 'desc')
                                            ->first();

                        if ($existingWarehouse) {
                            // update harga jual jika product yang baru masuk sudah ada di warehouse sebelumnya
                            $warehouse->selling_price = $existingWarehouse->selling_price;
                            $warehouse->save();
                        }
                    }
                } else {
                    // Jika is_received bernilai false, jangan lakukan apapun pada warehouse
                    $payment->status = $paymentStatus;
                }
            }
        } else {
            // Jika status payment bukan 'lunas' atau 'on progress', update status
            $payment->status = $paymentStatus;
            $payment->is_received = $isReceived;
        }

        // Simpan perubahan status
        $payment->save();

        // Simpan log perubahan status
        PaymentHistory::create([
            'user_id' => auth()->user()->id,
            'payment_id' => $payment->id,
            'action' => 'update',
            'old_data' => json_encode([
                'id' => $payment->id,
                'status' => $oldStatus,
                'is_received' => $oldReceived,
            ]),
            'new_data' => json_encode([
                'id' => $payment->id,
                'status' => $payment->status,
                'is_received' => $payment->is_received,
            ]),
        ]);

        return $payment;
    }
}
