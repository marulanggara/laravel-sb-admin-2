<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\InvoiceItem;

use DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'invoices';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'invoice_no',
        'created_by',
        'invoice_date',
        'due_date',
        'total_price',
        'created_at',
        'updated_at',
    ];

    // Relasi dengan invoice_items
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // Generate invoice_no dengan format INVXXXX tanpa duplikate
    public static function generateInvoiceNumber()
    {
        do {
            // Generate 6 angka acak
            $randomNumber = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Buat nomor invoice
            $invoiceNo = 'INV' . $randomNumber;
        } while (self::where('invoice_no', $invoiceNo)->exists()); // Jika nomor sudah ada acak lagi
          return $invoiceNo;
    }

    // Method untuk menghitung total quantity
    public function getTotalQuantityAttribute()
    {
        return $this->items->sum('quantity');
    }


    // Query get all invoices dengan invoice_items
    public static function getAllInvoices($perPage)
    {
        return DB::table('invoices')
            ->leftJoin('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->select(
                'invoices.id',
                'invoices.invoice_no',
                'invoices.created_by',
                'invoices.invoice_date',
                'invoices.due_date',
                'invoices.total_price',
                'invoices.created_at',
                DB::raw('SUM(invoice_items.total_price) as total_price'),
                DB::raw('SUM(invoice_items.quantity) as total_quantity'),
            )
            ->groupBy(
                'invoices.id',
                'invoices.invoice_no',
                'invoices.created_by',
                'invoices.invoice_date',
                'invoices.due_date',
                'invoices.total_price',
                'invoices.created_at'
            )
            ->whereNull('invoices.deleted_at')
            ->whereNull('invoice_items.deleted_at')
            ->orderBy('invoices.invoice_date', 'desc')
            ->paginate($perPage);
    }

    public static function addInvoice($request)
    {
        return DB::transaction(function () use ($request) {
            // Membuat invoice baru
            $invoice = Invoice::create([
                'invoice_no' => $request->invoice_no,
                'invoice_date' => now(),
                'due_date' => Carbon::parse($request->due_date)->setTimeFromTimeString(now()->toTimeString()),
                'created_by' => auth()->user()->name,
                'total_price' => 0,
                'created_at' => now(),
                'updated_at' => null,
            ]);

            $totalPrice = 0;
            
            foreach ($request->items as $item) {
                $remainingQuantity = $item['quantity']; // Total quantity yang diminta untuk invoice
                // Ambil warehouse untuk produk yang bersangkutan, urutkan berdasarkan 'created_at' (FIFO)
                $warehouses = Warehouse::where('product_id', $item['product_id'])
                    ->orderBy('created_at', 'asc') // FIFO
                    ->get();

                foreach ($warehouses as $warehouse) {
                    // Jika masih ada sisa quantity yang perlu dikurangi
                    if ($remainingQuantity > 0) {
                        $quantityToDecrement = min($remainingQuantity, $warehouse->quantity); // Ambil stok sesuai yang masih tersedia

                        // Kurangi quantity di warehouse
                        $warehouse->decrement('quantity', $quantityToDecrement);

                        // Hitung harga total untuk item yang dipilih
                        $totalItemPrice = $warehouse->selling_price * $quantityToDecrement;

                        // Simpan item ke invoice_item
                        InvoiceItem::create([
                            'invoice_id' => $invoice->id,
                            'warehouse_id' => $warehouse->id,
                            'product_id' => $warehouse->product_id,
                            'product_name' => $warehouse->product->name,
                            'product_code' => $warehouse->product->code,
                            'unit_name' => $warehouse->unit->name,
                            'quantity' => $quantityToDecrement,
                            'unit_price' => $warehouse->selling_price,
                            'total_price' => $totalItemPrice,
                            'created_at' => now(),
                            'updated_at' => null,
                        ]);

                        // Tambah harga total invoice
                        $totalPrice += $totalItemPrice;

                        // Kurangi sisa quantity yang perlu ditangani
                        $remainingQuantity -= $quantityToDecrement;
                    }

                    // Jika sudah cukup mengurangi quantity yang diperlukan, keluar dari loop
                    if ($remainingQuantity <= 0) {
                        break;
                    }
                }

                // Jika masih ada sisa quantity yang diminta dan stok tidak cukup, bisa throw exception atau error
                if ($remainingQuantity > 0) {
                    throw new \Exception('Not enough stock available for product: ' . $item['product_id']);
                }
            }

            // Update total price pada invoice
            $invoice->update(['total_price' => $totalPrice]);

            // Simpan log
            $logData = [
                'user_id' => auth()->user()->id,
                'invoice_id' => $invoice->id,
                'action' => 'create',
                'old_data' => json_encode([]),
                'new_data' => json_encode($invoice),
                'created_at' => now(),
            ];

            DB::table('log_histories.invoice_log_histories')->insert($logData);

            return $invoice;
        });
    }


    // Search Invoice By Invoice No
    public static function searchInvoice($search)
    {
        return DB::table('invoices')
        ->leftJoin('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
        ->select(
            'invoices.id',
            'invoices.invoice_no',
            'invoices.created_by',
            'invoices.invoice_date',
            'invoices.due_date',
            'invoices.total_price',
            'invoices.created_at',
            DB::raw('SUM(invoice_items.total_price) as total_price'),
            DB::raw('SUM(invoice_items.quantity) as total_quantity'),
        )
        ->where(function ($query) use ($search) {
            $query->where('invoice_no', 'ILIKE', '%' . $search . '%');
        })
        ->whereNull('invoices.deleted_at')
        ->whereNull('invoice_items.deleted_at')
        ->groupBy(
            'invoices.id',
            'invoices.invoice_no',
            'invoices.created_by',
            'invoices.invoice_date',
            'invoices.due_date',
            'invoices.total_price',
            'invoices.created_at'
        )
        ->orderBy('invoices.invoice_date', 'desc')
        ->paginate(25);
    }
}
