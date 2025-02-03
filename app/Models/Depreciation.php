<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Depreciation extends Model
{
    protected $fillable = [
        'building_age_from',
        'building_age_to',
        'depreciation_rate',
    ];
}
