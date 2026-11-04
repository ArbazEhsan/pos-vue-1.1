<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'manufactureds_id', 'qty', 'description'
    ];

    function manufactured()
    {
        return $this->belongsTo(Manufactureds::class);
    }

    function product()
    {
        return $this->belongsTo(Product::class);
    }
}
