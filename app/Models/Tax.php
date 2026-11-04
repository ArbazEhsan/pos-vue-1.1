<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_group_only' => 'boolean',
        'status' => 'boolean',
        'rate' => 'decimal:2',
    ];
}
