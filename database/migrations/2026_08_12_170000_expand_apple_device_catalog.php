<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $appleId = DB::table('brands')
            ->where('name', 'Apple')
            ->value('id');

        if (! $appleId) {
            throw new \RuntimeException('Apple brand not found.');
        }

        $storages = [
            '128GB' => 2,
            '256GB' => 3,
            '512GB' => 4,
            '1TB' => 5,
        ];

        foreach ($storages as $name => $sortOrder) {
            DB::table('storage_options')->updateOrInsert(
                ['name' => $name],
                [
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $colors = [
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

            'Midnight',
            'Starlight',
            '(PRODUCT)RED',
            'Alpine Green',
            'Space Black',
            'Deep Purple',
            'Purple',
            'Yellow',
            'Desert Titanium',
            'Teal',
            'Ultramarine',
        ];

        foreach ($colors as $index => $name) {
            DB::table('color_options')->updateOrInsert(
                ['name' => $name],
                [
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $models = [
            // Existing models corrected/expanded.
            'iPhone 13' => [
                'storages' => ['128GB', '256GB', '512GB'],
                'colors' => [
                    'Midnight',
                    'Starlight',
                    'Blue',
                    'Pink',
                    'Green',
                    '(PRODUCT)RED',
                ],
            ],

            'iPhone 13 Pro' => [
                'storages' => ['128GB', '256GB', '512GB', '1TB'],
                'colors' => [
                    'Graphite',
                    'Gold',
                    'Silver',
                    'Sierra Blue',
                    'Alpine Green',
                ],
            ],

            // New Apple models.
            'iPhone 13 Pro Max' => [
                'storages' => ['128GB', '256GB', '512GB', '1TB'],
                'colors' => [
                    'Graphite',
                    'Gold',
                    'Silver',
                    'Sierra Blue',
                    'Alpine Green',
                ],
            ],

            'iPhone 14' => [
                'storages' => ['128GB', '256GB', '512GB'],
                'colors' => [
                    'Midnight',
                    'Starlight',
                    '(PRODUCT)RED',
                    'Blue',
                    'Purple',
                    'Yellow',
                ],
            ],

            'iPhone 14 Pro' => [
                'storages' => ['128GB', '256GB', '512GB', '1TB'],
                'colors' => [
                    'Space Black',
                    'Silver',
                    'Gold',
                    'Deep Purple',
                ],
            ],

            'iPhone 14 Pro Max' => [
                'storages' => ['128GB', '256GB', '512GB', '1TB'],
                'colors' => [
                    'Space Black',
                    'Silver',
                    'Gold',
                    'Deep Purple',
                ],
            ],

            'iPhone 15' => [
                'storages' => ['128GB', '256GB', '512GB'],
                'colors' => [
                    'Black',
                    'Blue',
                    'Green',
                    'Yellow',
                    'Pink',
                ],
            ],

            // iPhone 15 Pro already exists; keep its existing catalog.
            'iPhone 15 Pro Max' => [
                'storages' => ['256GB', '512GB', '1TB'],
                'colors' => [
                    'Black Titanium',
                    'White Titanium',
                    'Blue Titanium',
                    'Natural Titanium',
                ],
            ],

            'iPhone 16' => [
                'storages' => ['128GB', '256GB', '512GB'],
                'colors' => [
                    'Black',
                    'White',
                    'Pink',
                    'Teal',
                    'Ultramarine',
                ],
            ],

            'iPhone 16 Pro' => [
                'storages' => ['128GB', '256GB', '512GB', '1TB'],
                'colors' => [
                    'Black Titanium',
                    'White Titanium',
                    'Natural Titanium',
                    'Desert Titanium',
                ],
            ],

            'iPhone 16 Pro Max' => [
                'storages' => ['256GB', '512GB', '1TB'],
                'colors' => [
                    'Black Titanium',
                    'White Titanium',
                    'Natural Titanium',
                    'Desert Titanium',
                ],
            ],
        ];

        $sortOrder = 1;

        foreach ($models as $modelName => $config) {
            DB::table('device_models')->updateOrInsert(
                [
                    'brand_id' => $appleId,
                    'name' => $modelName,
                ],
                [
                    'is_active' => true,
                    'sort_order' => $sortOrder++,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $modelId = DB::table('device_models')
                ->where('brand_id', $appleId)
                ->where('name', $modelName)
                ->value('id');

            DB::table('device_model_storage_option')
                ->where('device_model_id', $modelId)
                ->delete();

            foreach ($config['storages'] as $storageName) {
                $storageId = DB::table('storage_options')
                    ->where('name', $storageName)
                    ->value('id');

                DB::table('device_model_storage_option')->insert([
                    'device_model_id' => $modelId,
                    'storage_option_id' => $storageId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('device_model_color_option')
                ->where('device_model_id', $modelId)
                ->delete();

            foreach ($config['colors'] as $colorName) {
                $colorId = DB::table('color_options')
                    ->where('name', $colorName)
                    ->value('id');

                DB::table('device_model_color_option')->insert([
                    'device_model_id' => $modelId,
                    'color_option_id' => $colorId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $appleId = DB::table('brands')
            ->where('name', 'Apple')
            ->value('id');

        if (! $appleId) {
            return;
        }

        DB::table('device_models')
            ->where('brand_id', $appleId)
            ->whereIn('name', [
                'iPhone 13 Pro Max',
                'iPhone 14',
                'iPhone 14 Pro',
                'iPhone 14 Pro Max',
                'iPhone 15',
                'iPhone 15 Pro Max',
                'iPhone 16',
                'iPhone 16 Pro',
                'iPhone 16 Pro Max',
            ])
            ->delete();
    }
};
