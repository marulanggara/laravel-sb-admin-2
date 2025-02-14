<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Supplier;

class SupplierHistory extends Model
{
    use HasFactory;
    protected $table = 'log_histories.supplier_log_histories';
    protected $fillable = [
        'supplier_id',
        'user_id',
        'action',
        'old_data',
        'new_data',
    ];

    // Relasi dengan tabel supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Relasi dengan tabel user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Relasi dengan tabel supplier_
}
