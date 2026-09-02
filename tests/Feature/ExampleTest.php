<?php

it('shows the car landing page on home', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Home/CarLanding'));
});

it('redirects legacy cars url to home', function () {
    $this->get('/cars')
        ->assertRedirect('/');
});

it('shows the real estate coming soon page', function () {
    $this->get('/real-estate')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Home/RealEstate'));
});
