<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Payment;

class PaymentHistory extends Model
{
    use HasFactory;

    protected $table = 'log_histories.payment_log_histories';
    protected $fillable = [
        'payment_id',
        'user_id',
        'action',
        'old_data',
        'new_data',
    ];

    // Relasi dengan tabel payment
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    // Relasi dengan tabel user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
