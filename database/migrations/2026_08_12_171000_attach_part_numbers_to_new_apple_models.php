<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $models = [
        'iPhone 13 Pro Max',
        'iPhone 14',
        'iPhone 14 Pro',
        'iPhone 14 Pro Max',
        'iPhone 15',
        'iPhone 15 Pro Max',
        'iPhone 16',
        'iPhone 16 Pro',
        'iPhone 16 Pro Max',
    ];

    public function up(): void
    {
        $appleId = DB::table('brands')
            ->where('name', 'Apple')
            ->value('id');

        if (! $appleId) {
            throw new \RuntimeException('Apple brand not found.');
        }

        $partNumberIds = DB::table('part_number_options')
            ->whereIn('name', [
                'ZA/A',
                'LL/A',
                'CH/A',
                'ZP/A',
                'J/A',
                'AE/A',
            ])
            ->pluck('id');

        $modelIds = DB::table('device_models')
            ->where('brand_id', $appleId)
            ->whereIn('name', $this->models)
            ->pluck('id');

        foreach ($modelIds as $modelId) {
            foreach ($partNumberIds as $partNumberId) {
                DB::table('device_model_part_number_option')->insertOrIgnore([
                    'device_model_id' => $modelId,
                    'part_number_option_id' => $partNumberId,
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

        $modelIds = DB::table('device_models')
            ->where('brand_id', $appleId)
            ->whereIn('name', $this->models)
            ->pluck('id');

        DB::table('device_model_part_number_option')
            ->whereIn('device_model_id', $modelIds)
            ->delete();
    }
};
