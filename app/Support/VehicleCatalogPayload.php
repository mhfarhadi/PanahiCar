<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class VehicleCatalogPayload
{
    public static function make(): array
    {
        return [
            'brands' => DB::table('brands')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']),
            'models' => DB::table('device_models')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'brand_id', 'name']),
            'colors' => DB::table('color_options')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name']),
            'transmissions' => VehicleOptions::transmissions(),
            'fuelTypes' => VehicleOptions::fuelTypes(),
            'bodyConditions' => VehicleOptions::bodyConditions(),
        ];
    }
}
