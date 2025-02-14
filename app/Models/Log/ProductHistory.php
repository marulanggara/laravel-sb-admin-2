<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Product;

class ProductHistory extends Model
{
    use HasFactory;

    protected $table = 'log_histories.product_log_histories';
    protected $fillable = [
        'product_id',
        'user_id',
        'action',
        'old_data',
        'new_data',
    ];

    // Relasi dengan tabel product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    // Relasi dengan tabel user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
