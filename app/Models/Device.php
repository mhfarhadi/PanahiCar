<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'brand',
        'model',
        'model_year',
        'mileage',
        'storage',
        'color',
        'part_number',
        'transmission',
        'fuel_type',
        'insurance_months',
        'body_condition',
        'vin',
        'battery_health',
        'condition_grade',
        'imei',
        'registration_status',
        'status',
        'description',
        'announced_by_id',
        'announced_price',
        'announced_at',
        'created_by',
    ];
}
