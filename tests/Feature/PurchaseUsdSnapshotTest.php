<?php

use App\Models\User;
use App\Services\CurrencyRateService;
use Illuminate\Support\Facades\DB;

test('it stores the purchase date usd snapshot when buying an announced device', function () {
    $user = User::factory()->create();

    $sellerId = DB::table('contacts')->insertGetId([
        'name' => 'فروشنده تست',
        'mobile' => '09120000002',
        'contact_type' => 'colleague',
        'created_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $deviceId = DB::table('devices')->insertGetId([
        'brand' => 'Apple',
        'model' => 'iPhone 15 Pro',
        'storage' => '256GB',
        'status' => 'announced',
        'announced_by_id' => $sellerId,
        'announced_price' => 200_000_000,
        'announced_at' => now(),
        'created_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $purchaseDate = '2026-08-12';

    $this->mock(CurrencyRateService::class, function ($mock) use ($purchaseDate) {
        $mock->shouldReceive('snapshotForDate')
            ->once()
            ->with('USD', $purchaseDate)
            ->andReturn([
                'rate' => 187_100,
                'rate_date' => $purchaseDate,
                'source' => 'navasan',
            ]);
    });

    $response = $this
        ->actingAs($user)
        ->post(route('announced-devices.purchase.store', $deviceId), [
            'purchase_price' => 190_000_000,
            'purchase_date' => $purchaseDate,
        ]);

    $response->assertRedirect(route('devices.index'));

    $purchase = DB::table('purchases')
        ->where('device_id', $deviceId)
        ->first();

    expect($purchase)->not->toBeNull()
        ->and((int) $purchase->usd_rate)->toBe(187_100)
        ->and($purchase->usd_rate_date)->toBe($purchaseDate)
        ->and($purchase->usd_rate_source)->toBe('navasan')
        ->and(DB::table('devices')->where('id', $deviceId)->value('status'))
        ->toBe('in_stock');
});
