<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manufactureds extends Model
{
    use HasFactory;

    protected $fillable = [
        'day', 'manufacturing_date', 'product_id', 'subtract_qty_stage',
        'reference_no', 'recipe_id', 'quantity', 'wasted_quantity', 
        'production_cost', 'total_cost', 'status'
    ];

    function recipes()
    {
        return $this->hasMany(Recipe::class,);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
