<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'account_type',
        'password',
        'date_of_birth',
        'gender',
        'marital_status',
        'mobile',
        'permanent_address',
        'current_address',
        'bank_account_holder_name',
        'bank_account_no',
        'bank_name',
        'bank_branch_name',
        'bank_identifier_code',
        'basic_salary',
        'salary_frequency',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
