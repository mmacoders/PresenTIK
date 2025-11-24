<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'location_latitude',
        'location_longitude',
        'location_radius',
        'jam_masuk',
        'grace_period_minutes',
        'cutoff_time',
        'disable_location_validation', // Add this field
    ];

    protected $casts = [
        'location_latitude' => 'decimal:8',
        'location_longitude' => 'decimal:8',
        'location_radius' => 'integer',
        'grace_period_minutes' => 'integer',
        'disable_location_validation' => 'boolean', // Add casting
    ];
    
    // Remove the datetime casting for time fields as they're stored as TIME type in database
}