<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PartNumberSeeder extends Seeder
{
    public function run(): void
    {
        $parts = [
            'ZA/A',
            'LL/A',
            'CH/A',
            'ZP/A',
            'J/A',
            'AE/A',
        ];

        $partIds = [];

        foreach ($parts as $index => $part) {
            $partIds[$part] = DB::table('part_number_options')->insertGetId([
                'name' => $part,
                'is_active' => true,
                'sort_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $appleModels = DB::table('device_models')
            ->join('brands', 'brands.id', '=', 'device_models.brand_id')
            ->where('brands.name', 'Apple')
            ->select('device_models.id')
            ->get();

        foreach ($appleModels as $model) {
            foreach ($partIds as $partId) {
                DB::table('device_model_part_number_option')->insert([
                    'device_model_id' => $model->id,
                    'part_number_option_id' => $partId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
