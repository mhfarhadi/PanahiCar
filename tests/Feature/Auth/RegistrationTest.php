<?php

test('public registration screen is disabled', function () {
    $this->get('/register')
        ->assertNotFound();
});

test('public registration is disabled', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'register-test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertNotFound();

    $this->assertGuest();

    $this->assertDatabaseMissing('users', [
        'email' => 'register-test@example.com',
    ]);
});
