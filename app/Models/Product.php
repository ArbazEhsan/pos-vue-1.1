<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'name',
    //     'qty',
    //     'user_id',
    //     'purchase',
    //     'whole_sale',
    //     'retail',
    //     'category_id',
    //     'status',
    // ];

    protected $guarded = ['id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'barcode', 'barcode');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'barcode', 'barcode');
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function manufacture()
    {
        return $this->hasOne(Manufactureds::class);
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function combos()
    {
        return $this->hasMany(ProductCombo::class, 'product_id');
    }
}
