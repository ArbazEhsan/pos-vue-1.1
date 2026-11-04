<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariation;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = ['pur_no','day','barcode','qty','price','final','type'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'barcode', 'barcode');
    }

    public function pCounter()
    {
        return $this->belongsTo(PCounter::class, 'pur_no');
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'barcode', 'var_sku');
    }
}
