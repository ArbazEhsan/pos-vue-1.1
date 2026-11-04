<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxGroup extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'tax_ids' => 'array',
        'status' => 'boolean',
    ];

    public function taxes()
    {
        return $this->belongsToMany(Tax::class, 'tax_ids');
    }
    
    // Get taxes with details
    public function getTaxesAttribute()
    {
        if (empty($this->tax_ids)) {
            return collect();
        }
        return Tax::whereIn('id', $this->tax_ids)->get();
    }
}
