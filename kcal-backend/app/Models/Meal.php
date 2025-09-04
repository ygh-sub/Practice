<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    protected $fillable = ['name', 'portion', 'calories', 'date', 'is_estimated'];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'is_estimated' => 'boolean',
    ];
}
