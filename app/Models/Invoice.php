<?php

namespace App\Models;

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

    // Query add invoice
    public static function addInvoice($data)
    {
        return Invoice::create([
            'invoice_no' => $data['invoice_no'],
            'created_by' => $data['created_by'],
            'invoice_date' => $data['invoice_date'],
            'due_date' => $data['due_date'],
            'total_price' => $data['total_price'],
            'total_quantity' => $data['total_quantity'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
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
            $query->whereRaw('LOWER(invoices.invoice_no) LIKE ?', ['%'.strtolower($search).'%']);
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
