<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tran extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function ledgers()
    {
        return $this->hasMany(ledger::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

}
