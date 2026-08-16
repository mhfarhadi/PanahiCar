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
