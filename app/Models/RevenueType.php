<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenueType extends Model
{
    protected $fillable = [
        'code', 'name', 'description', 'is_active'
    ];
} 