<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleCatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('device_model_color_option')->delete();
        DB::table('device_model_storage_option')->delete();
        DB::table('device_model_part_number_option')->delete();
        DB::table('device_models')->delete();
        DB::table('brands')->delete();
        DB::table('color_options')->delete();

        $catalog = [
            'ایران‌خودرو' => [
                'sort_order' => 1,
                'models' => ['دنا پلاس', 'تارا', 'رانا پلاس', 'پژو 206', 'پژو 207', 'پژو پارس', 'سمند EF7', 'سورن پلاس', 'ری‌را'],
            ],
            'سایپا' => [
                'sort_order' => 2,
                'models' => ['تیبا 2', 'ساینا S', 'شاهین', 'اطلس', 'کوییک S', 'سهند', '151'],
            ],
            'مدیران خودرو' => [
                'sort_order' => 3,
                'models' => ['تیگو 7 پرو', 'X22', 'X33', 'آریزو 5', 'آریزو 6', 'MVM X55'],
            ],
            'کرمان موتور' => [
                'sort_order' => 4,
                'models' => ['JAC J4', 'KMC J7', 'KMC T8', 'JAC S5'],
            ],
            'بهمن موتور' => [
                'sort_order' => 5,
                'models' => ['فیدلیتی', 'دیگنیتی', 'فونیکس FX', 'ریسپکت'],
            ],
            'پارس‌خودرو' => [
                'sort_order' => 6,
                'models' => ['تندر 90', 'ساندرو', 'رنو تندر'],
            ],
            'زامیاد' => [
                'sort_order' => 7,
                'models' => ['زامیاد Z24', 'پادرا'],
            ],
        ];

        $brandIds = [];

        foreach ($catalog as $name => $meta) {
            $brandIds[$name] = DB::table('brands')->insertGetId([
                'name' => $name,
                'sort_order' => $meta['sort_order'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $modelIds = [];

        foreach ($catalog as $brandName => $meta) {
            foreach ($meta['models'] as $index => $modelName) {
                $modelIds[] = DB::table('device_models')->insertGetId([
                    'brand_id' => $brandIds[$brandName],
                    'name' => $modelName,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $colors = ['سفید', 'مشکی', 'نقره‌ای', 'خاکستری', 'آبی', 'قرمز', 'بژ'];
        $colorIds = [];

        foreach ($colors as $index => $color) {
            $colorIds[] = DB::table('color_options')->insertGetId([
                'name' => $color,
                'is_active' => true,
                'sort_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($modelIds as $modelId) {
            foreach ($colorIds as $colorId) {
                DB::table('device_model_color_option')->insert([
                    'device_model_id' => $modelId,
                    'color_option_id' => $colorId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
