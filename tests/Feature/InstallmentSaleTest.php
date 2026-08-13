<?php

use App\Models\User;
use App\Services\CurrencyRateService;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

test('it rounds installment checks to nearest ten thousand toman and keeps contract totals consistent', function () {
    $user = User::factory()->create();

    $buyerId = DB::table('contacts')->insertGetId([
        'name' => 'خریدار تست',
        'mobile' => '09120000001',
        'created_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $deviceId = DB::table('devices')->insertGetId([
        'brand' => 'Apple',
        'model' => 'iPhone 15 Pro',
        'storage' => '256GB',
        'status' => 'in_stock',
        'created_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $saleDate = '2026-08-13';

    $firstDueDate = Jalalian::fromCarbon(
        \Carbon\Carbon::parse($saleDate)
    )
        ->addMonths(1)
        ->toCarbon()
        ->toDateString();

    $this->mock(CurrencyRateService::class, function ($mock) use ($saleDate) {
        $mock->shouldReceive('snapshotForDate')
            ->once()
            ->with('USD', $saleDate)
            ->andReturn([
                'rate' => 190_000,
                'rate_date' => $saleDate,
                'source' => 'test',
            ]);
    });

    $response = $this
        ->actingAs($user)
        ->post(route('sales.store', $deviceId), [
            'buyer_id' => $buyerId,
            'sale_type' => 'installment',
            'sale_price' => 300_000_000,
            'down_payment' => 100_000_000,
            'monthly_profit_rate' => 6.5,
            'installment_count' => 3,
            'first_due_date' => $firstDueDate,
            'sale_date' => $saleDate,
        ]);

    $response->assertRedirect(route('sales.index'));

    $sale = DB::table('sales')
        ->where('device_id', $deviceId)
        ->first();

    expect($sale)->not->toBeNull()
        ->and((int) $sale->installment_profit)->toBe(39_010_000)
        ->and((int) $sale->contract_total)->toBe(339_010_000);

    $installments = DB::table('installments')
        ->where('sale_id', $sale->id)
        ->orderBy('installment_number')
        ->get();

    expect($installments)->toHaveCount(3)
        ->and($installments->pluck('amount')->map(fn ($amount) => (int) $amount)->all())
        ->toBe([
            79_670_000,
            79_670_000,
            79_670_000,
        ])
        ->and((int) $installments->sum('amount'))->toBe(239_010_000);
});
