<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WantedMarketBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        // Re-runnable: replace only our provisional market bootstrap rows.
        DB::table('wanted_device_requests')
            ->where('origin', 'bootstrap_market')
            ->delete();

        $usd = DB::table('exchange_rates')
            ->where('currency', 'USD')
            ->where('rate', '>', 0)
            ->orderByDesc('rate_date')
            ->orderByDesc('id')
            ->first();

        $rows = [
            // iPhone 13 128 — current asking-market reference roughly 74–95M.
            ['iPhone 13', '128GB', 'Midnight', 'A', 88, null, 'registered', 78_000_000, 'divar'],
            ['iPhone 13', '128GB', 'Starlight', 'A+', 92, null, 'registered', 81_000_000, 'sheypoor'],
            ['iPhone 13', '128GB', 'Blue', 'B', 84, null, 'registered', 76_000_000, 'divar'],
            ['iPhone 13', '128GB', 'Pink', 'A', 89, null, 'registered', 79_000_000, 'sheypoor'],
            ['iPhone 13', '128GB', 'Green', 'A+', 94, null, 'registered', 82_000_000, 'divar'],

            // iPhone 14 128 — deliberately below reasonable asking prices:
            // these are colleague BUY ceilings, not advertised sale prices.
            ['iPhone 14', '128GB', 'Midnight', 'A', 89, null, 'registered', 94_000_000, 'divar'],
            ['iPhone 14', '128GB', 'Purple', 'A+', 93, null, 'registered', 99_000_000, 'divar'],
            ['iPhone 14', '128GB', 'Blue', 'B', 85, null, 'registered', 91_000_000, 'divar'],

            // iPhone 15 128.
            ['iPhone 15', '128GB', 'Black', 'A', 91, null, 'registered', 103_000_000, 'divar'],
            ['iPhone 15', '128GB', 'Green', 'A+', 95, null, 'registered', 108_000_000, 'divar'],
            ['iPhone 15', '128GB', 'Blue', 'B', 86, null, 'registered', 96_000_000, 'divar'],

            // iPhone 15 Pro 256 — observed asking market roughly 125–160M.
            ['iPhone 15 Pro', '256GB', 'Natural Titanium', 'A', 91, null, 'registered', 122_000_000, 'divar'],
            ['iPhone 15 Pro', '256GB', 'Black Titanium', 'A+', 95, null, 'registered', 128_000_000, 'divar'],
            ['iPhone 15 Pro', '256GB', 'Blue Titanium', 'B', 87, null, 'registered', 116_000_000, 'divar'],

            // iPhone 15 Pro Max 256.
            ['iPhone 15 Pro Max', '256GB', 'Natural Titanium', 'A', 92, null, 'registered', 155_000_000, 'divar'],
            ['iPhone 15 Pro Max', '256GB', 'Black Titanium', 'A+', 96, null, 'registered', 162_000_000, 'divar'],

            // iPhone 16 128.
            ['iPhone 16', '128GB', 'Black', 'A+', 99, null, 'registered', 168_000_000, 'divar'],
            ['iPhone 16', '128GB', 'Ultramarine', 'A', 96, null, 'registered', 164_000_000, 'divar'],

            // Samsung: battery condition vocabulary instead of numeric health.
            ['Galaxy S23', '256GB', 'Black', 'A', null, 'good', 'registered', 60_000_000, 'divar'],
            ['Galaxy S24 Ultra', '256GB', 'Black', 'A+', null, 'excellent', 'registered', 156_000_000, 'divar'],
        ];

        $now = now();

        foreach ($rows as $index => $row) {
            [
                $model,
                $storage,
                $color,
                $condition,
                $batteryHealth,
                $batteryCondition,
                $registration,
                $price,
                $referenceSource,
            ] = $row;

            $brand = str_starts_with($model, 'Galaxy')
                ? 'Samsung'
                : 'Apple';

            DB::table('wanted_device_requests')->insert([
                'requester_name' => sprintf(
                    'نمونه بازار %02d',
                    $index + 1
                ),
                // Deliberately non-contact bootstrap identifiers.
                // Public UI must not expose these as callable colleagues.
                'requester_mobile' => sprintf(
                    '0000001%04d',
                    $index + 1
                ),
                'origin' => 'bootstrap_market',
                'market_reference_source' => $referenceSource,
                'brand' => $brand,
                'model' => $model,
                'storage' => $storage,
                'color' => $color,
                'condition_grade' => $condition,
                'registration_status' => $registration,
                'battery_health' => $batteryHealth,
                'battery_condition' => $batteryCondition,
                'max_price' => $price,
                'usd_rate' => $usd?->rate,
                'usd_rate_date' => $usd?->rate_date,
                'usd_rate_source' => $usd
                    ? 'bootstrap:'.$usd->source
                    : null,
                'description' => 'داده اولیه‌ی بازار برای جلوگیری از خالی‌بودن سیستم؛ شماره تماس واقعی نیست.',
                'created_at' => $now
                    ->copy()
                    ->subDays($index % 10)
                    ->subMinutes($index * 7),
                'updated_at' => $now
                    ->copy()
                    ->subDays($index % 10)
                    ->subMinutes($index * 7),
            ]);
        }
    }
}
