<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Invoice;

class InvoiceHistory extends Model
{
    use HasFactory;

    protected $table = 'log_histories.invoice_log_histories';
    protected $fillable = [
        'invoice_id',
        'user_id',
        'action',
        'old_data',
        'new_data',
    ];

    // Relasi dengan tabel invoice
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }

    // Relasi dengan tabel user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
