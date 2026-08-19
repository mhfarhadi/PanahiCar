<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\User;
use App\Support\AccessControl;
use App\Support\UserRoles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    public function index(): Response
    {
        $roles = collect(UserRoles::assignable())
            ->map(fn (string $role) => [
                'value' => $role,
                'label' => UserRoles::label($role),
            ])
            ->values();

        return Inertia::render('Organization/Index', [
            'locations' => Location::query()
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'is_active'])
                ->map(fn (Location $location) => [
                    ...$location->toArray(),
                    'users_count' => $location->users()->count(),
                    'devices_count' => $location->devices()->count(),
                ]),
            'users' => User::query()
                ->with('location:id,name')
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role', 'is_active', 'location_id', 'created_at'])
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'role_label' => UserRoles::label($user->role),
                    'is_active' => $user->is_active,
                    'location_id' => $user->location_id,
                    'location_name' => $user->location?->name,
                    'created_at' => $user->created_at?->toDateString(),
                ]),
            'roles' => $roles,
            'roleLabels' => UserRoles::labels(),
        ]);
    }

    public function storeLocation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:30', 'alpha_dash', 'unique:locations,code'],
        ]);

        Location::create([
            ...$validated,
            'code' => strtolower($validated['code']),
            'is_active' => true,
        ]);

        return back()->with('success', 'شعبه جدید ثبت شد.');
    }

    public function updateLocation(Request $request, Location $location): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('locations', 'code')->ignore($location->id)],
            'is_active' => ['required', 'boolean'],
        ]);

        $location->update([
            ...$validated,
            'code' => strtolower($validated['code']),
        ]);

        return back()->with('success', 'شعبه به‌روزرسانی شد.');
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(UserRoles::assignable())],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
        ]);

        User::create([
            ...$validated,
            'is_active' => true,
        ]);

        return back()->with('success', 'کاربر جدید ایجاد شد.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isSuperAdmin() && $user->id !== $request->user()->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::in(UserRoles::assignable())],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($user->isSuperAdmin()) {
            unset($validated['role'], $validated['location_id'], $validated['is_active']);
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        if ($user->id === $request->user()->id) {
            $validated['is_active'] = true;
        }

        $user->update($validated);

        return back()->with('success', 'کاربر به‌روزرسانی شد.');
    }
}
