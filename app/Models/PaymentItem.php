<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentItem extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'payment_items';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'payment_id',
        'product_id',
        'product_name',
        'product_code',
        'unit_id',
        'unit_name',
        'quantity',
        'total_price',
        'created_at',
        'updated_at',
    ];

    // Relasi payment item dengan tabel payment
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    // Relasi payment item dengan tabel product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    // Relasi payment item dengan tabel unit
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
