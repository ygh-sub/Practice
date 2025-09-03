<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    protected $fillable = ['name', 'calories', 'date'];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];
}
