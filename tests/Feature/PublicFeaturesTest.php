<?php

test('public features page is accessible without authentication', function () {
    $this
        ->get(route('features.index'))
        ->assertOk();
});
