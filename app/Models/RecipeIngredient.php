<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\RecipeHeader;
use App\Models\ProductVariation;

class RecipeIngredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_header_id', 'product_id', 'variation_id',
        'quantity', 'wastage_percent', 'unit_price', 'total_price'
    ];

    public function recipe()
    {
        return $this->belongsTo(RecipeHeader::class, 'recipe_header_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function variation() // Assuming you have a Variation model
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id'); // Adjust if Model name is differ
    }
}
