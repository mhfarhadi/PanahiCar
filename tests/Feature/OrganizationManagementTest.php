<?php

use App\Models\Device;
use App\Models\Location;
use App\Models\User;
use App\Support\UserRoles;

test('super admin can open organization page', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('organization.index'))
        ->assertOk();
});

test('branch staff cannot open organization page', function () {
    $location = Location::query()->create([
        'name' => 'شعبه تست',
        'code' => 'test-branch',
        'is_active' => true,
    ]);

    $staff = User::factory()->create([
        'role' => UserRoles::SALES,
        'location_id' => $location->id,
    ]);

    $this->actingAs($staff)
        ->get(route('organization.index'))
        ->assertForbidden();
});

test('super admin can create location and user', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post(route('organization.locations.store'), [
            'name' => 'شعبه شمال',
            'code' => 'north',
        ])
        ->assertRedirect();

    $location = Location::query()->where('code', 'north')->first();
    expect($location)->not->toBeNull();

    $this->actingAs($admin)
        ->post(route('organization.users.store'), [
            'name' => 'کارشناس فروش',
            'email' => 'sales@panahi.test',
            'password' => 'password123',
            'role' => UserRoles::SALES,
            'location_id' => $location->id,
        ])
        ->assertRedirect();

    $user = User::query()->where('email', 'sales@panahi.test')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(UserRoles::SALES)
        ->and($user->location_id)->toBe($location->id);
});

test('inventory user only sees devices from own location', function () {
    $main = Location::query()->create([
        'name' => 'شعبه یک',
        'code' => 'branch-one',
        'is_active' => true,
    ]);

    $second = Location::query()->create([
        'name' => 'شعبه دو',
        'code' => 'branch-two',
        'is_active' => true,
    ]);

    $inventoryUser = User::factory()->create([
        'role' => UserRoles::INVENTORY,
        'location_id' => $main->id,
    ]);

    $mainDevice = Device::query()->create([
        'brand' => 'ایران‌خودرو',
        'model' => '206',
        'model_year' => 1399,
        'mileage' => 120000,
        'transmission' => 'manual',
        'fuel_type' => 'petrol',
        'body_condition' => 'pristine',
        'status' => 'in_stock',
        'location_id' => $main->id,
    ]);

    $otherDevice = Device::query()->create([
        'brand' => 'سایپا',
        'model' => 'پراید',
        'model_year' => 1400,
        'mileage' => 100000,
        'transmission' => 'manual',
        'fuel_type' => 'petrol',
        'body_condition' => 'pristine',
        'status' => 'in_stock',
        'location_id' => $second->id,
    ]);

    $response = $this->actingAs($inventoryUser)
        ->get(route('devices.index'))
        ->assertOk();

    $deviceIds = collect($response->original->getData()['page']['props']['devices'])->pluck('id');

    expect($deviceIds->contains($mainDevice->id))->toBeTrue()
        ->and($deviceIds->contains($otherDevice->id))->toBeFalse();

    $this->actingAs($inventoryUser)
        ->get(route('devices.show', $otherDevice->id))
        ->assertForbidden();
});
