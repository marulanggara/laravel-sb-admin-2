<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Unit;

class UnitHistory extends Model
{
    use HasFactory;

    protected $table = 'log_histories.unit_log_histories';
    protected $fillable = [
        'unit_id',
        'user_id',
        'action',
        'old_data',
        'new_data',
    ];

    // Relasi dengan tabel product
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    // Relasi dengan tabel user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
