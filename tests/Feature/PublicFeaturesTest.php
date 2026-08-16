<?php

test('public features page is accessible without authentication', function () {
    $this
        ->get(route('features.index'))
        ->assertOk();
});

test('public installment contract page is accessible without authentication', function () {
    $this
        ->get(route('features.contracts.index'))
        ->assertOk();
});


test('public price estimate page is accessible without authentication', function () {
    $this
        ->get(route('features.price-estimates.index'))
        ->assertOk();
});

test('public gold collateral calculator is accessible without authentication', function () {
    $this
        ->get(route('features.gold-collateral.index'))
        ->assertOk();
});

test('public wanted device page is accessible without authentication', function () {
    $this
        ->get(route('features.wanted.index'))
        ->assertOk();
});

test('public wanted market page is accessible without authentication', function () {
    $this
        ->get(route('features.wanted-market.index'))
        ->assertOk();
});

test('public check printer page is accessible without authentication', function () {
    $this
        ->get(route('features.check-printer.index'))
        ->assertOk();
});

test('wanted market feed does not expose organic mobile numbers in initial page props', function () {
    $id = \Illuminate\Support\Facades\DB::table('wanted_device_requests')
        ->insertGetId([
            'requester_name' => 'همکار تست',
            'requester_mobile' => '09121234567',
            'origin' => 'organic',
            'brand' => 'Apple',
            'model' => 'iPhone 13',
            'storage' => '128GB',
            'condition_grade' => 'A',
            'registration_status' => 'registered',
            'battery_health' => 90,
            'max_price' => 80_000_000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    $response = $this->get(route('features.wanted-market.index'));

    $response
        ->assertOk()
        ->assertDontSee('09121234567');

    expect($id)->toBeInt();
});

test('organic wanted request contact can be revealed explicitly', function () {
    $id = \Illuminate\Support\Facades\DB::table('wanted_device_requests')
        ->insertGetId([
            'requester_name' => 'همکار تست',
            'requester_mobile' => '09121234567',
            'origin' => 'organic',
            'brand' => 'Apple',
            'model' => 'iPhone 13',
            'storage' => '128GB',
            'condition_grade' => 'A',
            'registration_status' => 'registered',
            'battery_health' => 90,
            'max_price' => 80_000_000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    $this
        ->getJson(route('features.wanted-market.contact', $id))
        ->assertOk()
        ->assertJsonPath('contact.requester_name', 'همکار تست')
        ->assertJsonPath('contact.mobile', '09121234567');
});

test('bootstrap wanted rows never reveal a contact number', function () {
    $id = \Illuminate\Support\Facades\DB::table('wanted_device_requests')
        ->insertGetId([
            'requester_name' => 'نمونه بازار',
            'requester_mobile' => '00000010001',
            'origin' => 'bootstrap_market',
            'market_reference_source' => 'divar',
            'brand' => 'Apple',
            'model' => 'iPhone 13',
            'storage' => '128GB',
            'condition_grade' => 'A',
            'registration_status' => 'registered',
            'battery_health' => 90,
            'max_price' => 80_000_000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    $this
        ->getJson(route('features.wanted-market.contact', $id))
        ->assertStatus(422)
        ->assertJsonMissing(['mobile' => '00000010001']);
});
