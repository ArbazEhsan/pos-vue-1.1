<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = ['day','barcode','qty','user_id'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'barcode', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
