<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Counter extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'sale_no');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'cust_id');
    }

    public function deliveryOrder()
    {
        return $this->hasOne(DeliveryOrder::class);
    }
}
