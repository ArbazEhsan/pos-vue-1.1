<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RecipeIngredient;
use App\Models\User;

class RecipeHeader extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'instructions', 'total_output_quantity', 
        'wastage_percent', 'production_cost', 'status', 'user_id'
    ];

    public function ingredients()
    {
        return $this->hasMany(RecipeIngredient::class, 'recipe_header_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
