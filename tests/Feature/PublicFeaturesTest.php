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
