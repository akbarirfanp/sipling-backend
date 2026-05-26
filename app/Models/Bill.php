<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Bill extends Model
{
    use HasFactory;

    protected $keyType = 'string';      // tambahin ini
    public $incrementing = false; 

    protected $fillable = [
        'user_id',
        'invoice_number',
        'gross_amount',
        'status',
        'due_date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function billDetails(): HasMany
    {
        return $this->hasMany(BillDetail::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
    
    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'invoice_number';
    }

    protected static function booted()
    {
        static::creating(function ($bill) {
            if (empty($bill->id)) {
                $bill->id = (string) Str::uuid();
            }
            
            if (empty($bill->invoice_number)) {
                $bill->invoice_number = 'SIPLING-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
            }
        });
    }
}