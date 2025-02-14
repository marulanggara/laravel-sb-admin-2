<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Warehouse;

class WarehouseHistory extends Model
{
    use HasFactory;

    protected $table = 'log_histories.warehouse_log_histories';
    protected $fillable = [
        'warehouse_id',
        'user_id',
        'action',
        'old_data',
        'new_data',
    ];

    // Relasi dengan tabel warehouse
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }
    

    // Relasi dengan tabel user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
