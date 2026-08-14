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

test('a mistakenly paid check can be safely reopened with an append only audit note', function () {
    $user = User::factory()->create();

    $buyerId = DB::table('contacts')->insertGetId([
        'name' => 'خریدار تست اصلاح وصول',
        'mobile' => '09120000005',
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
            'sale_price' => 400_000_000,
            'down_payment' => 100_000_000,
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

    $target = $installments[0];
    $other = $installments[1];

    $saleSnapshot = [
        'sale_price' => (int) $sale->sale_price,
        'down_payment' => (int) $sale->down_payment,
        'installment_profit' => (int) $sale->installment_profit,
        'contract_total' => (int) $sale->contract_total,
    ];

    $otherSnapshot = [
        'amount' => (int) $other->amount,
        'paid_amount' => (int) $other->paid_amount,
        'status' => $other->status,
        'paid_at' => $other->paid_at,
    ];

    $this
        ->actingAs($user)
        ->from(route('installments.index'))
        ->post(
            route('installments.mark-paid', $target->id),
            ['paid_at' => '2026-10-20']
        )
        ->assertSessionHasNoErrors();

    $paid = DB::table('installments')
        ->where('id', $target->id)
        ->first();

    expect($paid->status)->toBe('paid')
        ->and((int) $paid->paid_amount)->toBe((int) $target->amount)
        ->and($paid->paid_at)->toBe('2026-10-20');

    $notesAfterPayment = DB::table('entity_notes')
        ->where('entity_type', 'installment')
        ->where('entity_id', $target->id)
        ->pluck('body')
        ->all();

    expect(collect($notesAfterPayment)->contains(
        fn ($body) => str_contains($body, 'وصول چک ثبت شد')
            && str_contains($body, '2026-10-20')
    ))->toBeTrue();

    // Reversal reason is mandatory; invalid request must preserve paid state.
    $this
        ->actingAs($user)
        ->from(route('installments.index'))
        ->post(
            route('installments.reverse-paid', $target->id),
            ['reason' => '']
        )
        ->assertSessionHasErrors('reason');

    $stillPaid = DB::table('installments')
        ->where('id', $target->id)
        ->first();

    expect($stillPaid->status)->toBe('paid')
        ->and((int) $stillPaid->paid_amount)->toBe((int) $target->amount)
        ->and($stillPaid->paid_at)->toBe('2026-10-20');

    $reason = 'پاس شدن این چک اشتباهی ثبت شده بود.';

    $this
        ->actingAs($user)
        ->from(route('installments.index'))
        ->post(
            route('installments.reverse-paid', $target->id),
            ['reason' => $reason]
        )
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('installments.index'));

    $reopened = DB::table('installments')
        ->where('id', $target->id)
        ->first();

    expect($reopened->status)->toBe('pending')
        ->and((int) $reopened->paid_amount)->toBe(0)
        ->and($reopened->paid_at)->toBeNull();

    $auditNotes = DB::table('entity_notes')
        ->where('entity_type', 'installment')
        ->where('entity_id', $target->id)
        ->orderBy('id')
        ->pluck('body')
        ->all();

    expect($auditNotes)->toHaveCount(2)
        ->and(collect($auditNotes)->contains(
            fn ($body) => str_contains($body, 'اصلاح وصول چک')
                && str_contains($body, '2026-10-20')
                && str_contains($body, $reason)
        ))->toBeTrue();

    $unchangedOther = DB::table('installments')
        ->where('id', $other->id)
        ->first();

    expect((int) $unchangedOther->amount)->toBe($otherSnapshot['amount'])
        ->and((int) $unchangedOther->paid_amount)->toBe($otherSnapshot['paid_amount'])
        ->and($unchangedOther->status)->toBe($otherSnapshot['status'])
        ->and($unchangedOther->paid_at)->toBe($otherSnapshot['paid_at']);

    $unchangedSale = DB::table('sales')
        ->where('id', $sale->id)
        ->first();

    expect((int) $unchangedSale->sale_price)->toBe($saleSnapshot['sale_price'])
        ->and((int) $unchangedSale->down_payment)->toBe($saleSnapshot['down_payment'])
        ->and((int) $unchangedSale->installment_profit)->toBe($saleSnapshot['installment_profit'])
        ->and((int) $unchangedSale->contract_total)->toBe($saleSnapshot['contract_total']);

    // Repeating reversal on an already-open check must not add another audit entry.
    $this
        ->actingAs($user)
        ->from(route('installments.index'))
        ->post(
            route('installments.reverse-paid', $target->id),
            ['reason' => 'درخواست تکراری']
        )
        ->assertSessionHasNoErrors();

    expect(
        DB::table('entity_notes')
            ->where('entity_type', 'installment')
            ->where('entity_id', $target->id)
            ->count()
    )->toBe(2);
});

test('check images can be removed and replaced without destroying historical files', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $buyerId = DB::table('contacts')->insertGetId([
        'name' => 'خریدار تست آرشیو تصویر',
        'mobile' => '09120000006',
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
            'sale_price' => 300_000_000,
            'down_payment' => 100_000_000,
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

    $firstImage = UploadedFile::fake()
        ->image('first-check.jpg', 1000, 700)
        ->size(400);

    $secondImage = UploadedFile::fake()
        ->image('second-check.jpg', 1000, 700)
        ->size(400);

    $this
        ->actingAs($user)
        ->post(
            route('installments.check-details', $installment->id),
            ['images' => [$firstImage, $secondImage]]
        )
        ->assertSessionHasNoErrors();

    $images = DB::table('installment_images')
        ->where('installment_id', $installment->id)
        ->orderBy('id')
        ->get();

    expect($images)->toHaveCount(2);

    $removedImage = $images[0];
    $replacedImage = $images[1];

    Storage::disk('public')->assertExists($removedImage->image_path);
    Storage::disk('public')->assertExists($replacedImage->image_path);

    $removeReason = 'این تصویر اشتباهی برای این چک بارگذاری شده بود.';

    $this
        ->actingAs($user)
        ->from(route('installments.index'))
        ->post(
            route('installments.images.remove', [
                'installment' => $installment->id,
                'image' => $removedImage->id,
            ]),
            ['reason' => $removeReason]
        )
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('installments.index'));

    $archived = DB::table('installment_images')
        ->where('id', $removedImage->id)
        ->first();

    expect($archived->removed_at)->not->toBeNull()
        ->and((int) $archived->removed_by)->toBe($user->id)
        ->and($archived->removal_reason)->toBe($removeReason);

    // Historical evidence remains physically stored.
    Storage::disk('public')->assertExists($removedImage->image_path);

    $replacement = UploadedFile::fake()
        ->image('replacement-check.jpg', 1000, 700)
        ->size(450);

    $replaceReason = 'تصویر واضح‌تر چک جایگزین شد.';

    $this
        ->actingAs($user)
        ->from(route('installments.index'))
        ->post(
            route('installments.images.replace', [
                'installment' => $installment->id,
                'image' => $replacedImage->id,
            ]),
            [
                'image' => $replacement,
                'reason' => $replaceReason,
            ]
        )
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('installments.index'));

    $oldReplaced = DB::table('installment_images')
        ->where('id', $replacedImage->id)
        ->first();

    expect($oldReplaced->removed_at)->not->toBeNull()
        ->and((int) $oldReplaced->removed_by)->toBe($user->id)
        ->and($oldReplaced->removal_reason)->toBe($replaceReason);

    Storage::disk('public')->assertExists($replacedImage->image_path);

    $activeImages = DB::table('installment_images')
        ->where('installment_id', $installment->id)
        ->whereNull('removed_at')
        ->get();

    expect($activeImages)->toHaveCount(1)
        ->and((int) $activeImages[0]->sort_order)
        ->toBe((int) $replacedImage->sort_order);

    Storage::disk('public')->assertExists($activeImages[0]->image_path);

    $notes = DB::table('entity_notes')
        ->where('entity_type', 'installment')
        ->where('entity_id', $installment->id)
        ->pluck('body')
        ->all();

    expect(collect($notes)->contains(
        fn ($body) => str_contains($body, 'آرشیو')
            && str_contains($body, $removeReason)
    ))->toBeTrue()
        ->and(collect($notes)->contains(
            fn ($body) => str_contains($body, 'جایگزین شد')
                && str_contains($body, $replaceReason)
        ))->toBeTrue();
});
