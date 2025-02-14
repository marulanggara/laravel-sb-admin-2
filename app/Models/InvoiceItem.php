<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceItem extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'invoice_items';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'invoice_id',
        'warehouse_id',
        'product_id',
        'product_name',
        'product_code',
        'unit_name',
        'quantity',
        'unit_price',
        'total_price',
        'created_at'
    ];

    // Relasi dengan tabel invoices
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // Relasi dengan tabel warehouses
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    // Relasi dengan tabel products
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
