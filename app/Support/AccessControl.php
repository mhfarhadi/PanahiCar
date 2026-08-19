<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class AccessControl
{
    public const ORG_MANAGE = 'org.manage';

    public const DEVICES_VIEW = 'devices.view';

    public const DEVICES_MANAGE = 'devices.manage';

    public const SALES_VIEW = 'sales.view';

    public const SALES_MANAGE = 'sales.manage';

    public const INSTALLMENTS_VIEW = 'installments.view';

    public const INSTALLMENTS_MANAGE = 'installments.manage';

    public const CONTACTS_VIEW = 'contacts.view';

    public const CONTACTS_MANAGE = 'contacts.manage';

    public const ANNOUNCED_VIEW = 'announced.view';

    public const ANNOUNCED_MANAGE = 'announced.manage';

    /** @var array<string, list<string>> */
    private const ROLE_PERMISSIONS = [
        UserRoles::SUPER_ADMIN => [
            self::ORG_MANAGE,
            self::DEVICES_VIEW,
            self::DEVICES_MANAGE,
            self::SALES_VIEW,
            self::SALES_MANAGE,
            self::INSTALLMENTS_VIEW,
            self::INSTALLMENTS_MANAGE,
            self::CONTACTS_VIEW,
            self::CONTACTS_MANAGE,
            self::ANNOUNCED_VIEW,
            self::ANNOUNCED_MANAGE,
        ],
        UserRoles::MANAGER => [
            self::DEVICES_VIEW,
            self::DEVICES_MANAGE,
            self::SALES_VIEW,
            self::SALES_MANAGE,
            self::INSTALLMENTS_VIEW,
            self::INSTALLMENTS_MANAGE,
            self::CONTACTS_VIEW,
            self::CONTACTS_MANAGE,
            self::ANNOUNCED_VIEW,
            self::ANNOUNCED_MANAGE,
        ],
        UserRoles::SALES => [
            self::DEVICES_VIEW,
            self::SALES_VIEW,
            self::SALES_MANAGE,
            self::INSTALLMENTS_VIEW,
            self::CONTACTS_VIEW,
            self::CONTACTS_MANAGE,
            self::ANNOUNCED_VIEW,
        ],
        UserRoles::INVENTORY => [
            self::DEVICES_VIEW,
            self::DEVICES_MANAGE,
            self::CONTACTS_VIEW,
            self::ANNOUNCED_VIEW,
            self::ANNOUNCED_MANAGE,
        ],
        UserRoles::ACCOUNTANT => [
            self::DEVICES_VIEW,
            self::SALES_VIEW,
            self::INSTALLMENTS_VIEW,
            self::INSTALLMENTS_MANAGE,
            self::CONTACTS_VIEW,
        ],
        UserRoles::VIEWER => [
            self::DEVICES_VIEW,
            self::SALES_VIEW,
            self::INSTALLMENTS_VIEW,
            self::CONTACTS_VIEW,
            self::ANNOUNCED_VIEW,
        ],
    ];

    public static function can(User $user, string $permission): bool
    {
        return in_array($permission, self::permissionsFor($user), true);
    }

    /** @return list<string> */
    public static function permissionsFor(User $user): array
    {
        if ($user->role === 'staff') {
            return self::ROLE_PERMISSIONS[UserRoles::MANAGER];
        }

        return self::ROLE_PERMISSIONS[$user->role] ?? [];
    }

    public static function managesAllLocations(User $user): bool
    {
        return $user->role === UserRoles::SUPER_ADMIN;
    }

    public static function locationId(User $user): ?int
    {
        if (self::managesAllLocations($user)) {
            return null;
        }

        return $user->location_id;
    }

    public static function applyLocationScope(Builder $query, User $user, string $column = 'location_id'): Builder
    {
        $locationId = self::locationId($user);

        if ($locationId === null) {
            return $query;
        }

        return $query->where($column, $locationId);
    }

    public static function assertDeviceAccess(User $user, ?int $locationId): void
    {
        if (self::managesAllLocations($user)) {
            return;
        }

        abort_unless($locationId !== null && $locationId === $user->location_id, 403);
    }

    public static function resolveLocationIdForCreate(User $user): int
    {
        if ($user->location_id) {
            return $user->location_id;
        }

        return (int) DB::table('locations')
            ->where('is_active', true)
            ->orderBy('id')
            ->value('id');
    }
}
