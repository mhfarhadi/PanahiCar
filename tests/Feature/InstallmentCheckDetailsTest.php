<?php

use App\Models\User;
use App\Services\CurrencyRateService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Morilog\Jalali\Jalalian;

test('it stores installment check details images and append only notes', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $buyerId = DB::table('contacts')->insertGetId([
        'name' => 'خریدار تست چک',
        'mobile' => '09120000002',
        'created_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $deviceId = DB::table('devices')->insertGetId([
        'brand' => 'Apple',
        'model' => 'iPhone 15',
        'storage' => '128GB',
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

    $this
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
        ])
        ->assertRedirect(route('sales.index'));

    $sale = DB::table('sales')
        ->where('device_id', $deviceId)
        ->first();

    $installment = DB::table('installments')
        ->where('sale_id', $sale->id)
        ->orderBy('installment_number')
        ->first();

    expect($installment)->not->toBeNull();

    DB::table('entity_notes')->insert([
        'entity_type' => 'installment',
        'entity_id' => $installment->id,
        'body' => 'یادداشت قبلی',
        'created_by' => $user->id,
        'created_at' => now()->subMinute(),
        'updated_at' => now()->subMinute(),
    ]);

    $image = UploadedFile::fake()
        ->image('check-front.jpg', 1200, 800)
        ->size(500);

    $response = $this
        ->actingAs($user)
        ->from(route('installments.index'))
        ->post(
            route('installments.check-details', $installment->id),
            [
                'bank_name' => 'بانک ملت',
                'check_number' => '۱۲۳۴۵۶۷۸۹',
                'sayad_id' => '۱۲۳۴۵۶۷۸۹۰۱۲۳۴۵۶',
                'images' => [$image],
                'note' => 'تصویر چک و اطلاعات صیاد ثبت شد.',
            ]
        );

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('installments.index'));

    $updated = DB::table('installments')
        ->where('id', $installment->id)
        ->first();

    expect($updated->bank_name)->toBe('بانک ملت')
        ->and($updated->check_number)->toBe('123456789')
        ->and($updated->sayad_id)->toBe('1234567890123456');

    $storedImage = DB::table('installment_images')
        ->where('installment_id', $installment->id)
        ->first();

    expect($storedImage)->not->toBeNull()
        ->and((int) $storedImage->uploaded_by)->toBe($user->id);

    Storage::disk('public')->assertExists($storedImage->image_path);

    $notes = DB::table('entity_notes')
        ->where('entity_type', 'installment')
        ->where('entity_id', $installment->id)
        ->orderBy('id')
        ->pluck('body')
        ->all();

    expect($notes)->toContain('یادداشت قبلی')
        ->and($notes)->toContain('تصویر چک و اطلاعات صیاد ثبت شد.')
        ->and($notes)->toHaveCount(2);
});

test('sayad id must contain exactly sixteen digits when provided', function () {
    $user = User::factory()->create();

    $buyerId = DB::table('contacts')->insertGetId([
        'name' => 'خریدار اعتبارسنجی چک',
        'mobile' => '09120000003',
        'created_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $deviceId = DB::table('devices')->insertGetId([
        'brand' => 'Samsung',
        'model' => 'Galaxy S24',
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

    $this
        ->actingAs($user)
        ->post(route('sales.store', $deviceId), [
            'buyer_id' => $buyerId,
            'sale_type' => 'installment',
            'sale_price' => 200_000_000,
            'down_payment' => 50_000_000,
            'monthly_profit_rate' => 6.5,
            'installment_count' => 2,
            'first_due_date' => $firstDueDate,
            'sale_date' => $saleDate,
        ])
        ->assertRedirect(route('sales.index'));

    $sale = DB::table('sales')
        ->where('device_id', $deviceId)
        ->first();

    $installment = DB::table('installments')
        ->where('sale_id', $sale->id)
        ->first();

    $this
        ->actingAs($user)
        ->from(route('installments.index'))
        ->post(
            route('installments.check-details', $installment->id),
            [
                'bank_name' => 'بانک ملت',
                'sayad_id' => '۱۲۳۴۵۶۷۸۹۰',
            ]
        )
        ->assertSessionHasErrors('sayad_id');

    expect(
        DB::table('installments')
            ->where('id', $installment->id)
            ->value('sayad_id')
    )->toBeNull();
});

test('marking an installment paid stores the real paid date without rewriting financial history', function () {
    $user = User::factory()->create();

    $buyerId = DB::table('contacts')->insertGetId([
        'name' => 'خریدار تست وصول چک',
        'mobile' => '09120000004',
        'created_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $deviceId = DB::table('devices')->insertGetId([
        'brand' => 'Apple',
        'model' => 'iPhone 16',
        'storage' => '256GB',
        'status' => 'in_stock',
        'created_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $saleDate = '2026-08-14';

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

    $this
        ->actingAs($user)
        ->post(route('sales.store', $deviceId), [
            'buyer_id' => $buyerId,
            'sale_type' => 'installment',
            'sale_price' => 320_000_000,
            'down_payment' => 80_000_000,
            'monthly_profit_rate' => 6.5,
            'installment_count' => 2,
            'first_due_date' => $firstDueDate,
            'sale_date' => $saleDate,
        ])
        ->assertRedirect(route('sales.index'));

    $sale = DB::table('sales')
        ->where('device_id', $deviceId)
        ->first();

    $installments = DB::table('installments')
        ->where('sale_id', $sale->id)
        ->orderBy('installment_number')
        ->get();

    expect($installments)->toHaveCount(2);

    $firstInstallment = $installments[0];
    $secondInstallment = $installments[1];

    $saleFinancialSnapshot = [
        'sale_price' => (int) $sale->sale_price,
        'down_payment' => (int) $sale->down_payment,
        'installment_profit' => (int) $sale->installment_profit,
        'contract_total' => (int) $sale->contract_total,
    ];

    $secondInstallmentSnapshot = [
        'amount' => (int) $secondInstallment->amount,
        'paid_amount' => (int) $secondInstallment->paid_amount,
        'status' => $secondInstallment->status,
        'paid_at' => $secondInstallment->paid_at,
    ];

    $paidAt = '2026-10-20';

    $this
        ->actingAs($user)
        ->from(route('installments.index'))
        ->post(
            route('installments.mark-paid', $firstInstallment->id),
            ['paid_at' => $paidAt]
        )
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('installments.index'));

    $paidInstallment = DB::table('installments')
        ->where('id', $firstInstallment->id)
        ->first();

    expect($paidInstallment->status)->toBe('paid')
        ->and((int) $paidInstallment->paid_amount)
        ->toBe((int) $firstInstallment->amount)
        ->and($paidInstallment->paid_at)->toBe($paidAt);

    $unchangedSecondInstallment = DB::table('installments')
        ->where('id', $secondInstallment->id)
        ->first();

    expect((int) $unchangedSecondInstallment->amount)
        ->toBe($secondInstallmentSnapshot['amount'])
        ->and((int) $unchangedSecondInstallment->paid_amount)
        ->toBe($secondInstallmentSnapshot['paid_amount'])
        ->and($unchangedSecondInstallment->status)
        ->toBe($secondInstallmentSnapshot['status'])
        ->and($unchangedSecondInstallment->paid_at)
        ->toBe($secondInstallmentSnapshot['paid_at']);

    $unchangedSale = DB::table('sales')
        ->where('id', $sale->id)
        ->first();

    expect((int) $unchangedSale->sale_price)
        ->toBe($saleFinancialSnapshot['sale_price'])
        ->and((int) $unchangedSale->down_payment)
        ->toBe($saleFinancialSnapshot['down_payment'])
        ->and((int) $unchangedSale->installment_profit)
        ->toBe($saleFinancialSnapshot['installment_profit'])
        ->and((int) $unchangedSale->contract_total)
        ->toBe($saleFinancialSnapshot['contract_total']);

    // A repeated request must not rewrite the original clearance date.
    $this
        ->actingAs($user)
        ->from(route('installments.index'))
        ->post(
            route('installments.mark-paid', $firstInstallment->id),
            ['paid_at' => '2026-10-25']
        )
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('installments.index'));

    $afterRepeatedRequest = DB::table('installments')
        ->where('id', $firstInstallment->id)
        ->first();

    expect($afterRepeatedRequest->status)->toBe('paid')
        ->and((int) $afterRepeatedRequest->paid_amount)
        ->toBe((int) $firstInstallment->amount)
        ->and($afterRepeatedRequest->paid_at)->toBe($paidAt);
});
