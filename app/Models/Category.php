<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    use HasFactory;

    // protected $guarded = ['id'];
    // Define which attributes are mass assignable
    protected $fillable = ['id', 'name', 'parent_id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'status', // Add 'status' here if you have that column in your database
        'parent_id', // You might also want to hide the foreign key itself
        'user_id',
    ];


    // Assuming your table name is 'categories' (Laravel default)
    // and your primary key is 'id'
    protected $primaryKey = 'id';

    // The foreign key name for the parent ID column in the DB
    const PARENT_ID = 'parent_id';

    // Define the relationship for children
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, self::PARENT_ID, $this->primaryKey)
                    ->with('children');
    }

    // Define the relationship for the parent
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, self::PARENT_ID, $this->primaryKey);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
