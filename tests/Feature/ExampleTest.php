<?php

it('shows the division splash on home', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Home/Splash'));
});

it('shows the car landing page', function () {
    $this->get('/cars')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Home/CarLanding'));
});

it('shows the real estate coming soon page', function () {
    $this->get('/real-estate')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Home/RealEstate'));
});
