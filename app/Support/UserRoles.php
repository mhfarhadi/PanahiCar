<?php

namespace App\Support;

class UserRoles
{
    public const SUPER_ADMIN = 'super_admin';

    public const MANAGER = 'manager';

    public const SALES = 'sales';

    public const INVENTORY = 'inventory';

    public const ACCOUNTANT = 'accountant';

    public const VIEWER = 'viewer';

    /** @return list<string> */
    public static function assignable(): array
    {
        return [
            self::MANAGER,
            self::SALES,
            self::INVENTORY,
            self::ACCOUNTANT,
            self::VIEWER,
        ];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::SUPER_ADMIN => 'مدیر کل',
            self::MANAGER => 'مدیر شعبه',
            self::SALES => 'کارشناس فروش',
            self::INVENTORY => 'مسئول موجودی',
            self::ACCOUNTANT => 'حسابداری',
            self::VIEWER => 'مشاهده‌گر',
        ];
    }

    public static function label(string $role): string
    {
        return self::labels()[$role] ?? $role;
    }
}
