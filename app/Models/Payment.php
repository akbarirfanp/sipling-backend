<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Bill;

class Payment extends Model
{
    use HasFactory;

    protected $keyType = 'string';      // tambahin ini
    public $incrementing = false; 

    protected $fillable = [
        'id',
        'bill_id',
        'amount',
        'status',
        'payment_date',
        'snap_token'
    ];

    protected $casts = [
        'payment_date' => 'datetime',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }
}