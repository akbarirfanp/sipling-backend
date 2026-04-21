<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        'id',
        'reference_name',
        'status',
        'action',
        'created_by',
        'created_at',
    ];
}
