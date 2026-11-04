<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariation;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = ['sale_no','day','barcode','qty','price','final','type'];

    public function counter()
    {
        return $this->belongsTo(Counter::class, 'sale_no');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'barcode', 'barcode');
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'barcode', 'var_sku');
    }
}
