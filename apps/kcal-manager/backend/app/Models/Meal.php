<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    protected $fillable = ['name', 'portion', 'calories', 'date', 'is_estimated', 'user_id'];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'is_estimated' => 'boolean',
    ];

    /**
     * Get the user that owns the meal
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
