<?php

use App\Models\User;
use App\Services\PriceEstimationService;
use Illuminate\Support\Facades\DB;

function createEstimatorSale(array $deviceOverrides = [], array $saleOverrides = []): int
{
    $user = User::factory()->create();

    $contactId = DB::table('contacts')->insertGetId([
        'name' => 'Test Buyer '.uniqid(),
        'mobile' => '09'.random_int(100000000, 999999999),
        'created_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $deviceId = DB::table('devices')->insertGetId(array_merge([
        'brand' => 'Apple',
        'model' => 'iPhone 15 Pro',
        'storage' => '256GB',
        'condition_grade' => 'A',
        'battery_health' => 95,
        'registration_status' => 'registered',
        'status' => 'sold',
        'created_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ], $deviceOverrides));

    DB::table('sales')->insert(array_merge([
        'device_id' => $deviceId,
        'buyer_id' => $contactId,
        'sale_type' => 'cash',
        'sale_price' => 100_000_000,
        'down_payment' => 0,
        'sale_date' => '2026-08-01',
        'usd_rate' => 100_000,
        'usd_rate_date' => '2026-08-01',
        'usd_rate_source' => 'test',
        'created_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ], $saleOverrides));

    return $deviceId;
}

test('it normalizes comparable sale prices using current usd rate and returns the median', function () {
    createEstimatorSale(
        [],
        [
            'sale_price' => 100_000_000,
            'usd_rate' => 100_000,
        ]
    );

    createEstimatorSale(
        [],
        [
            'sale_price' => 180_000_000,
            'usd_rate' => 150_000,
        ]
    );

    $result = app(PriceEstimationService::class)->estimate(
        'Apple',
        'iPhone 15 Pro',
        '256GB',
        200_000
    );

    expect($result['available'])->toBeTrue()
        ->and($result['comparable_count'])->toBe(2)
        ->and($result['range_min'])->toBe(200_000_000)
        ->and($result['range_max'])->toBe(240_000_000)
        ->and($result['estimate'])->toBe(220_000_000)
        ->and($result['confidence'])->toBe('low')
        ->and($result['specification_adjusted'])->toBeFalse();
});

test('it only uses exact brand model and storage comparables', function () {
    createEstimatorSale();

    createEstimatorSale(
        ['storage' => '512GB'],
        ['sale_price' => 500_000_000]
    );

    createEstimatorSale(
        ['model' => 'iPhone 15'],
        ['sale_price' => 400_000_000]
    );

    $result = app(PriceEstimationService::class)->estimate(
        'Apple',
        'iPhone 15 Pro',
        '256GB',
        100_000
    );

    expect($result['available'])->toBeTrue()
        ->and($result['comparable_count'])->toBe(1)
        ->and($result['estimate'])->toBe(100_000_000);
});

test('it prefers more similar device specifications without fixed price adjustments', function () {
    createEstimatorSale(
        [
            'condition_grade' => 'A+',
            'battery_health' => 100,
            'registration_status' => 'registered',
        ],
        [
            'sale_price' => 190_000_000,
            'usd_rate' => 190_000,
        ]
    );

    createEstimatorSale(
        [
            'condition_grade' => 'C',
            'battery_health' => 70,
            'registration_status' => 'unregistered',
        ],
        [
            'sale_price' => 260_000_000,
            'usd_rate' => 190_000,
        ]
    );

    $result = app(PriceEstimationService::class)->estimate(
        'Apple',
        'iPhone 15 Pro',
        '256GB',
        190_000,
        'A+',
        100,
        null,
        'registered'
    );

    expect($result['available'])->toBeTrue()
        ->and($result['specification_adjusted'])->toBeTrue()
        ->and($result['estimate'])->toBe(190_000_000)
        ->and($result['comparables'][0]->similarity_score)->toBe(100)
        ->and($result['comparables'][0]->normalized_price)->toBe(190_000_000)
        ->and($result['comparables'][1]->similarity_score)->toBeLessThan(100);
});

test('it reports unavailable when there are no exact comparables', function () {
    $result = app(PriceEstimationService::class)->estimate(
        'Apple',
        'iPhone 16 Pro',
        '1TB',
        200_000
    );

    expect($result['available'])->toBeFalse()
        ->and($result['reason'])->toBe('no_exact_comparables')
        ->and($result['comparable_count'])->toBe(0);
});
