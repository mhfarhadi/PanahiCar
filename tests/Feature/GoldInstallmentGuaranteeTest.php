<?php

use App\Models\User;
use App\Services\CurrencyRateService;
use App\Services\GoldRateService;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

test('gold backed installment sale keeps installments and blocks check registration', function () {
    $user = User::factory()->create();

    $buyerId = DB::table('contacts')->insertGetId([
        'name' => 'خریدار ضمانت طلا',
        'mobile' => '09121112233',
        'created_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $deviceId = DB::table('devices')->insertGetId([
        'brand' => 'Apple',
        'model' => 'iPhone 16 Pro',
        'storage' => '256GB',
        'status' => 'in_stock',
        'created_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $saleDate = '2026-08-16';

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

    $this->mock(GoldRateService::class, function ($mock) use ($saleDate) {
        $mock->shouldReceive('snapshotForDate')
            ->once()
            ->with($saleDate)
            ->andReturn([
                'rate_per_gram' => 19_052_130,
                'rate_date' => $saleDate,
                'source' => 'test',
            ]);
    });

    $this
        ->actingAs($user)
        ->post(route('sales.store', $deviceId), [
            'buyer_id' => $buyerId,
            'sale_type' => 'installment',
            'guarantee_type' => 'gold',
            'sale_price' => 140_000_000,
            'down_payment' => 40_000_000,
            'monthly_profit_rate' => 6.5,
            'installment_count' => 3,
            'first_due_date' => $firstDueDate,
            'sale_date' => $saleDate,
            'gold_received_weight' => 6,
            'gold_type' => 'دستبند',
            'gold_description' => 'یک عدد دستبند به عنوان وثیقه',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('sales.index'));

    $sale = DB::table('sales')
        ->where('device_id', $deviceId)
        ->first();

    expect($sale)->not->toBeNull()
        ->and($sale->guarantee_type)->toBe('gold');

    $collateral = DB::table('sale_gold_collaterals')
        ->where('sale_id', $sale->id)
        ->first();

    expect($collateral)->not->toBeNull()
        ->and((int) $collateral->base_principal)->toBe(100_000_000)
        ->and((int) $collateral->coverage_profit)->toBe(13_000_000)
        ->and((int) $collateral->coverage_amount)->toBe(113_000_000)
        ->and((float) $collateral->required_weight)->toBe(5.9311)
        ->and((float) $collateral->received_weight)->toBe(6.0)
        ->and($collateral->gold_type)->toBe('دستبند');

    $installments = DB::table('installments')
        ->where('sale_id', $sale->id)
        ->orderBy('installment_number')
        ->get();

    expect($installments)->toHaveCount(3);

    $this
        ->actingAs($user)
        ->from(route('installments.index'))
        ->post(
            route('installments.check-details', $installments[0]->id),
            [
                'bank_name' => 'بانک ملت',
                'check_number' => '123456',
            ]
        )
        ->assertSessionHasErrors('check_number');

    expect(
        DB::table('installments')
            ->where('id', $installments[0]->id)
            ->value('check_number')
    )->toBeNull();
});

test('gold backed sale rejects collateral lighter than calculated coverage', function () {
    $user = User::factory()->create();

    $buyerId = DB::table('contacts')->insertGetId([
        'name' => 'خریدار طلای ناکافی',
        'mobile' => '09124445566',
        'created_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $deviceId = DB::table('devices')->insertGetId([
        'brand' => 'Samsung',
        'model' => 'Galaxy S25',
        'storage' => '256GB',
        'status' => 'in_stock',
        'created_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $saleDate = '2026-08-16';

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
            ->andReturn(null);
    });

    $this->mock(GoldRateService::class, function ($mock) use ($saleDate) {
        $mock->shouldReceive('snapshotForDate')
            ->once()
            ->with($saleDate)
            ->andReturn([
                'rate_per_gram' => 19_052_130,
                'rate_date' => $saleDate,
                'source' => 'test',
            ]);
    });

    $this
        ->actingAs($user)
        ->post(route('sales.store', $deviceId), [
            'buyer_id' => $buyerId,
            'sale_type' => 'installment',
            'guarantee_type' => 'gold',
            'sale_price' => 140_000_000,
            'down_payment' => 40_000_000,
            'monthly_profit_rate' => 6.5,
            'installment_count' => 3,
            'first_due_date' => $firstDueDate,
            'sale_date' => $saleDate,
            'gold_received_weight' => 5,
            'gold_type' => 'زنجیر',
        ])
        ->assertSessionHasErrors('gold_received_weight');

    expect(
        DB::table('sales')
            ->where('device_id', $deviceId)
            ->exists()
    )->toBeFalse();
});
