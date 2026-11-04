<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ledgers()
    {
        return $this->hasMany(ledger::class);
    }

    public function transactions()
    {
        return $this->hasMany(Tran::class);
    }
}
