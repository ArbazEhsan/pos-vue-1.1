<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'counter_id',
        'rider_id',
        'status',
        'preparing_at',
        'transit_at',
        'delivered_at',
        'canceled_at',
        'returned_at',
        'notes',
    ];

    protected $casts = [
        'preparing_at' => 'datetime',
        'preparing_due_at' => 'integer',
        'delivery_due_at' => 'integer',
    ];

    public function counter()
    {
        return $this->belongsTo(Counter::class);
    }

    public function rider()
    {
        return $this->belongsTo(Account::class, 'rider_id');
    }
}
