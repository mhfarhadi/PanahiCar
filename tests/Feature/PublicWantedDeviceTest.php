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
            'max_price' => 85_000_000,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('storage');
});
