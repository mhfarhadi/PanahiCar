<?php

use Illuminate\Support\Facades\DB;

function createWantedCatalogFixture(): array
{
    $brandId = DB::table('brands')->insertGetId([
        'name' => 'Wanted Test Brand',
        'is_active' => true,
        'sort_order' => 999,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $modelId = DB::table('device_models')->insertGetId([
        'brand_id' => $brandId,
        'name' => 'Wanted Test Model',
        'is_active' => true,
        'sort_order' => 999,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $storageId = DB::table('storage_options')->insertGetId([
        'name' => '999GB-test',
        'is_active' => true,
        'sort_order' => 999,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $colorId = DB::table('color_options')->insertGetId([
        'name' => 'Wanted Test Color',
        'is_active' => true,
        'sort_order' => 999,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('device_model_storage_option')->insert([
        'device_model_id' => $modelId,
        'storage_option_id' => $storageId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('device_model_color_option')->insert([
        'device_model_id' => $modelId,
        'color_option_id' => $colorId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [
        'brand' => 'Wanted Test Brand',
        'model' => 'Wanted Test Model',
        'storage' => '999GB-test',
        'color' => 'Wanted Test Color',
    ];
}

test('public wanted device page is accessible without authentication', function () {
    $this
        ->get(route('features.wanted.index'))
        ->assertOk();
});

test('public colleague can submit a structured wanted device request', function () {
    $catalog = createWantedCatalogFixture();

    $this
        ->postJson(route('features.wanted.store'), [
            'requester_name' => 'همکار تست',
            'requester_mobile' => '۰۹۱۲ ۱۲۳ ۴۵۶۷',
            'brand' => $catalog['brand'],
            'model' => $catalog['model'],
            'storage' => $catalog['storage'],
            'color' => $catalog['color'],
            'condition_grade' => 'A',
            'registration_status' => 'registered',
            'battery_health' => 92,
            'max_price' => 85_000_000,
            'description' => 'جعبه برایم مهم است.',
        ])
        ->assertCreated()
        ->assertJsonPath('request.model', $catalog['model'])
        ->assertJsonPath('request.max_price', 85_000_000);

    $this->assertDatabaseHas('wanted_device_requests', [
        'requester_name' => 'همکار تست',
        'requester_mobile' => '09121234567',
        'brand' => $catalog['brand'],
        'model' => $catalog['model'],
        'storage' => $catalog['storage'],
        'color' => $catalog['color'],
        'condition_grade' => 'A',
        'registration_status' => 'registered',
        'battery_health' => 92,
        'max_price' => 85_000_000,
    ]);

    $this->assertDatabaseMissing('contacts', [
        'mobile' => '09121234567',
    ]);
});

test('wanted request rejects storage that does not belong to selected model', function () {
    $catalog = createWantedCatalogFixture();

    DB::table('storage_options')->insert([
        'name' => '888GB-invalid',
        'is_active' => true,
        'sort_order' => 998,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this
        ->postJson(route('features.wanted.store'), [
            'requester_name' => 'همکار تست',
            'requester_mobile' => '09121234567',
            'brand' => $catalog['brand'],
            'model' => $catalog['model'],
            'storage' => '888GB-invalid',
            'condition_grade' => 'A',
            'registration_status' => 'registered',
            'battery_health' => 90,
            'max_price' => 85_000_000,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('storage');
});

test('gross price outlier is rejected once enough unique colleague demand exists', function () {
    $catalog = createWantedCatalogFixture();

    DB::table('exchange_rates')->insert([
        'currency' => 'USD',
        'rate' => 100_000,
        'rate_date' => now()->toDateString(),
        'source' => 'test',
        'fetched_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach (range(1, 5) as $index) {
        DB::table('wanted_device_requests')->insert([
            'requester_name' => "همکار {$index}",
            'requester_mobile' => '0912000000'.$index,
            'brand' => $catalog['brand'],
            'model' => $catalog['model'],
            'storage' => $catalog['storage'],
            'color' => null,
            'condition_grade' => 'A',
            'registration_status' => 'registered',
            'battery_health' => 90,
            'battery_condition' => null,
            'max_price' => 80_000_000,
            'usd_rate' => 100_000,
            'usd_rate_date' => now()->toDateString(),
            'usd_rate_source' => 'test',
            'description' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $this
        ->postJson(route('features.wanted.store'), [
            'requester_name' => 'قیمت پرت',
            'requester_mobile' => '09129999999',
            'brand' => $catalog['brand'],
            'model' => $catalog['model'],
            'storage' => $catalog['storage'],
            'condition_grade' => 'A',
            'registration_status' => 'registered',
            'battery_health' => 90,
            'max_price' => 10_000_000,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('max_price');

    $this->assertDatabaseMissing('wanted_device_requests', [
        'requester_mobile' => '09129999999',
        'max_price' => 10_000_000,
    ]);
});

test('repeated requests from one mobile do not manufacture market consensus', function () {
    $catalog = createWantedCatalogFixture();

    foreach (range(1, 7) as $index) {
        DB::table('wanted_device_requests')->insert([
            'requester_name' => 'یک همکار تکراری',
            'requester_mobile' => '09121111111',
            'brand' => $catalog['brand'],
            'model' => $catalog['model'],
            'storage' => $catalog['storage'],
            'color' => null,
            'condition_grade' => null,
            'registration_status' => null,
            'battery_health' => null,
            'battery_condition' => null,
            'max_price' => 80_000_000,
            'usd_rate' => null,
            'usd_rate_date' => null,
            'usd_rate_source' => null,
            'description' => null,
            'created_at' => now()->subMinutes($index),
            'updated_at' => now()->subMinutes($index),
        ]);
    }

    $this
        ->postJson(route('features.wanted.store'), [
            'requester_name' => 'سیگنال جدید',
            'requester_mobile' => '09122222222',
            'brand' => $catalog['brand'],
            'model' => $catalog['model'],
            'storage' => $catalog['storage'],
            'condition_grade' => 'A',
            'registration_status' => 'registered',
            'battery_health' => 90,
            'max_price' => 10_000_000,
        ])
        ->assertCreated();

    $this->assertDatabaseHas('wanted_device_requests', [
        'requester_mobile' => '09122222222',
        'max_price' => 10_000_000,
    ]);
});

test('wanted request stores immutable usd snapshot when current rate exists', function () {
    $catalog = createWantedCatalogFixture();

    $this->mock(\App\Services\CurrencyRateService::class, function ($mock) {
        $mock->shouldReceive('snapshotForDate')
            ->once()
            ->with('USD', now()->toDateString())
            ->andReturn([
                'rate' => 123_456,
                'rate_date' => now()->toDateString(),
                'source' => 'test_snapshot',
            ]);
    });

    $this
        ->postJson(route('features.wanted.store'), [
            'requester_name' => 'همکار نرخ',
            'requester_mobile' => '09123334444',
            'brand' => $catalog['brand'],
            'model' => $catalog['model'],
            'storage' => $catalog['storage'],
            'condition_grade' => 'A',
            'registration_status' => 'registered',
            'battery_health' => 90,
            'max_price' => 80_000_000,
        ])
        ->assertCreated();

    $this->assertDatabaseHas('wanted_device_requests', [
        'requester_mobile' => '09123334444',
        'usd_rate' => 123_456,
        'usd_rate_date' => now()->toDateString(),
        'usd_rate_source' => 'test_snapshot',
    ]);
});

test('bootstrap market rows do not reject a plausible aggressive colleague bid as consensus', function () {
    $catalog = createWantedCatalogFixture();

    foreach (range(1, 5) as $index) {
        DB::table('wanted_device_requests')->insert([
            'requester_name' => 'Bootstrap '.$index,
            'requester_mobile' => '0000002000'.$index,
            'origin' => 'bootstrap_market',
            'market_reference_source' => 'divar',
            'brand' => $catalog['brand'],
            'model' => $catalog['model'],
            'storage' => $catalog['storage'],
            'color' => null,
            'condition_grade' => null,
            'registration_status' => null,
            'battery_health' => null,
            'battery_condition' => null,
            'max_price' => 80_000_000,
            'usd_rate' => null,
            'usd_rate_date' => null,
            'usd_rate_source' => null,
            'description' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $this->mock(\App\Services\CurrencyRateService::class, function ($mock) {
        $mock->shouldReceive('snapshotForDate')
            ->once()
            ->andReturn(null);
    });

    $this
        ->postJson(route('features.wanted.store'), [
            'requester_name' => 'همکار واقعی',
            'requester_mobile' => '09125554444',
            'brand' => $catalog['brand'],
            'model' => $catalog['model'],
            'storage' => $catalog['storage'],
            'condition_grade' => 'A',
            'registration_status' => 'registered',
            'battery_health' => 90,
            'max_price' => 55_000_000,
        ])
        ->assertCreated();

    $this->assertDatabaseHas('wanted_device_requests', [
        'requester_mobile' => '09125554444',
        'origin' => 'organic',
        'max_price' => 55_000_000,
    ]);
});

test('gross price is rejected when provisional demand and a completed sale independently corroborate the outlier', function () {
    $catalog = createWantedCatalogFixture();

    $user = \App\Models\User::factory()->create();

    $buyerId = DB::table('contacts')->insertGetId([
        'name' => 'خریدار تست guard',
        'mobile' => '09120008888',
        'created_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $deviceId = DB::table('devices')->insertGetId([
        'brand' => $catalog['brand'],
        'model' => $catalog['model'],
        'storage' => $catalog['storage'],
        'condition_grade' => 'A',
        'battery_health' => 90,
        'registration_status' => 'registered',
        'status' => 'sold',
        'created_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('sales')->insert([
        'device_id' => $deviceId,
        'buyer_id' => $buyerId,
        'sale_type' => 'cash',
        'sale_price' => 100_000_000,
        'down_payment' => 0,
        'sale_date' => now()->toDateString(),
        'usd_rate' => 100_000,
        'usd_rate_date' => now()->toDateString(),
        'usd_rate_source' => 'test',
        'created_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach ([78_000_000, 79_000_000, 80_000_000, 81_000_000, 82_000_000] as $index => $price) {
        DB::table('wanted_device_requests')->insert([
            'requester_name' => 'Bootstrap '.$index,
            'requester_mobile' => '0000009000'.($index + 1),
            'origin' => 'bootstrap_market',
            'market_reference_source' => 'divar',
            'brand' => $catalog['brand'],
            'model' => $catalog['model'],
            'storage' => $catalog['storage'],
            'color' => null,
            'condition_grade' => 'A',
            'registration_status' => 'registered',
            'battery_health' => 90,
            'battery_condition' => null,
            'max_price' => $price,
            'usd_rate' => 100_000,
            'usd_rate_date' => now()->toDateString(),
            'usd_rate_source' => 'bootstrap:test',
            'description' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $this->mock(\App\Services\CurrencyRateService::class, function ($mock) {
        $mock->shouldReceive('snapshotForDate')
            ->once()
            ->with('USD', now()->toDateString())
            ->andReturn([
                'rate' => 100_000,
                'rate_date' => now()->toDateString(),
                'source' => 'test',
            ]);
    });

    $this
        ->postJson(route('features.wanted.store'), [
            'requester_name' => 'قیمت فاحش',
            'requester_mobile' => '09129990000',
            'brand' => $catalog['brand'],
            'model' => $catalog['model'],
            'storage' => $catalog['storage'],
            'condition_grade' => 'A',
            'registration_status' => 'registered',
            'battery_health' => 90,
            'max_price' => 10_000_000,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('max_price');

    $this->assertDatabaseMissing('wanted_device_requests', [
        'requester_mobile' => '09129990000',
        'max_price' => 10_000_000,
    ]);
});

test('wanted request requires condition registration and battery evidence', function () {
    $catalog = createWantedCatalogFixture();

    $this
        ->postJson(route('features.wanted.store'), [
            'requester_name' => 'مشخصات ناقص',
            'requester_mobile' => '۰۹۱۲۳۳۳۲۲۱۱',
            'brand' => $catalog['brand'],
            'model' => $catalog['model'],
            'storage' => $catalog['storage'],
            'max_price' => 80_000_000,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'condition_grade',
            'registration_status',
            'battery_health',
        ]);
});

test('provisional market signal rejects an obviously absurd price even without a completed sale', function () {
    $catalog = createWantedCatalogFixture();

    foreach ([78_000_000, 79_000_000, 80_000_000, 81_000_000, 82_000_000] as $index => $price) {
        DB::table('wanted_device_requests')->insert([
            'requester_name' => 'Bootstrap sanity '.$index,
            'requester_mobile' => '0000011000'.($index + 1),
            'origin' => 'bootstrap_market',
            'market_reference_source' => 'divar',
            'brand' => $catalog['brand'],
            'model' => $catalog['model'],
            'storage' => $catalog['storage'],
            'color' => null,
            'condition_grade' => 'A',
            'registration_status' => 'registered',
            'battery_health' => 90,
            'battery_condition' => null,
            'max_price' => $price,
            'usd_rate' => 100_000,
            'usd_rate_date' => now()->toDateString(),
            'usd_rate_source' => 'bootstrap:test',
            'description' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $this->mock(\App\Services\CurrencyRateService::class, function ($mock) {
        $mock->shouldReceive('snapshotForDate')
            ->once()
            ->andReturn([
                'rate' => 100_000,
                'rate_date' => now()->toDateString(),
                'source' => 'test',
            ]);
    });

    $this
        ->postJson(route('features.wanted.store'), [
            'requester_name' => 'قیمت غیرواقعی',
            'requester_mobile' => '۰۹۱۲۹۹۹۸۸۷۷',
            'brand' => $catalog['brand'],
            'model' => $catalog['model'],
            'storage' => $catalog['storage'],
            'condition_grade' => 'A',
            'registration_status' => 'registered',
            'battery_health' => 90,
            'max_price' => 2_000_000,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('max_price');

    $this->assertDatabaseMissing('wanted_device_requests', [
        'requester_mobile' => '09129998877',
        'max_price' => 2_000_000,
    ]);
});

test('same model demand in another storage catches an absurd price when exact storage has no history', function () {
    $catalog = createWantedCatalogFixture();

    $brandId = DB::table('brands')
        ->where('name', $catalog['brand'])
        ->value('id');

    $modelId = DB::table('device_models')
        ->where('brand_id', $brandId)
        ->where('name', $catalog['model'])
        ->value('id');

    $otherStorageId = DB::table('storage_options')->insertGetId([
        'name' => '888GB-model-fallback',
        'is_active' => true,
        'sort_order' => 998,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('device_model_storage_option')->insert([
        'device_model_id' => $modelId,
        'storage_option_id' => $otherStorageId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach (
        [116_000_000, 120_000_000, 124_000_000, 128_000_000]
        as $index => $price
    ) {
        DB::table('wanted_device_requests')->insert([
            'requester_name' => 'Bootstrap other storage '.$index,
            'requester_mobile' => '0000022000'.($index + 1),
            'origin' => 'bootstrap_market',
            'market_reference_source' => 'divar',
            'brand' => $catalog['brand'],
            'model' => $catalog['model'],
            'storage' => '888GB-model-fallback',
            'color' => null,
            'condition_grade' => 'A',
            'registration_status' => 'registered',
            'battery_health' => 90,
            'battery_condition' => null,
            'max_price' => $price,
            'usd_rate' => 100_000,
            'usd_rate_date' => now()->toDateString(),
            'usd_rate_source' => 'bootstrap:test',
            'description' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $this->mock(
        \App\Services\CurrencyRateService::class,
        function ($mock) {
            $mock->shouldReceive('snapshotForDate')
                ->once()
                ->andReturn([
                    'rate' => 100_000,
                    'rate_date' => now()->toDateString(),
                    'source' => 'test',
                ]);
        }
    );

    $this
        ->postJson(route('features.wanted.store'), [
            'requester_name' => 'قیمت پرت حافظه جدید',
            'requester_mobile' => '۰۹۱۲۸۸۸۷۷۶۶',
            'brand' => $catalog['brand'],
            'model' => $catalog['model'],
            'storage' => $catalog['storage'],
            'condition_grade' => 'A',
            'registration_status' => 'registered',
            'battery_health' => 90,
            'max_price' => 2_000_000,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('max_price');

    $this->assertDatabaseMissing('wanted_device_requests', [
        'requester_mobile' => '09128887766',
        'max_price' => 2_000_000,
    ]);
});
