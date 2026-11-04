<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PCounter extends Model
{
    use HasFactory;
    protected $table = 'pcounters';
    public $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'return_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'pur_no');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'cust_id');
    }
}
