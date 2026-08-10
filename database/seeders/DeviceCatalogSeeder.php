<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeviceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Brands
        $appleId = DB::table('brands')->insertGetId([
            'name' => 'Apple',
            'is_active' => true,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $samsungId = DB::table('brands')->insertGetId([
            'name' => 'Samsung',
            'is_active' => true,
            'sort_order' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Storage options
        $storages = [];

        foreach (['64GB', '128GB', '256GB', '512GB', '1TB'] as $index => $storage) {
            $storages[$storage] = DB::table('storage_options')->insertGetId([
                'name' => $storage,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Colors
        $colors = [];

        foreach ([
            'Black',
            'White',
            'Blue',
            'Green',
            'Pink',
            'Graphite',
            'Gold',
            'Silver',
            'Sierra Blue',
            'Natural Titanium',
            'Black Titanium',
            'Blue Titanium',
            'White Titanium',
        ] as $index => $color) {
            $colors[$color] = DB::table('color_options')->insertGetId([
                'name' => $color,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Models
        $models = [
            [
                'brand_id' => $appleId,
                'name' => 'iPhone 13',
                'storages' => ['128GB', '256GB', '512GB'],
                'colors' => ['Black', 'White', 'Blue', 'Pink'],
            ],
            [
                'brand_id' => $appleId,
                'name' => 'iPhone 13 Pro',
                'storages' => ['128GB', '256GB', '512GB', '1TB'],
                'colors' => ['Graphite', 'Gold', 'Silver', 'Sierra Blue'],
            ],
            [
                'brand_id' => $appleId,
                'name' => 'iPhone 15 Pro',
                'storages' => ['128GB', '256GB', '512GB', '1TB'],
                'colors' => [
                    'Natural Titanium',
                    'Black Titanium',
                    'Blue Titanium',
                    'White Titanium',
                ],
            ],
            [
                'brand_id' => $samsungId,
                'name' => 'Galaxy S23',
                'storages' => ['128GB', '256GB'],
                'colors' => ['Black', 'Green'],
            ],
            [
                'brand_id' => $samsungId,
                'name' => 'Galaxy S24 Ultra',
                'storages' => ['256GB', '512GB', '1TB'],
                'colors' => ['Black', 'Green'],
            ],
        ];

        foreach ($models as $index => $model) {
            $modelId = DB::table('device_models')->insertGetId([
                'brand_id' => $model['brand_id'],
                'name' => $model['name'],
                'is_active' => true,
                'sort_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($model['storages'] as $storage) {
                DB::table('device_model_storage_option')->insert([
                    'device_model_id' => $modelId,
                    'storage_option_id' => $storages[$storage],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($model['colors'] as $color) {
                DB::table('device_model_color_option')->insert([
                    'device_model_id' => $modelId,
                    'color_option_id' => $colors[$color],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
