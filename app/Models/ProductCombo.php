<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCombo extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function comboProduct()
    {
        return $this->belongsTo(Product::class, 'combo_product_id');
    }

    public function comboVariation()
    {
        return $this->belongsTo(ProductVariation::class, 'combo_variation_id');
    }
}
