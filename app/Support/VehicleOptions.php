<?php

namespace App\Support;

class VehicleOptions
{
    public static function transmissions(): array
    {
        return [
            'manual' => 'دنده‌ای',
            'automatic' => 'اتومات',
        ];
    }

    public static function fuelTypes(): array
    {
        return [
            'petrol' => 'بنزین',
            'dual_fuel' => 'دوگانه‌سوز',
            'diesel' => 'دیزل',
            'hybrid' => 'هیبرید',
        ];
    }

    public static function bodyConditions(): array
    {
        return [
            'pristine' => 'بی‌رنگ / سالم',
            'one_spot' => 'یک لکه رنگ',
            'two_spot' => 'دو لکه رنگ',
            'multi_spot' => 'چند لکه رنگ',
            'paintless_dent' => 'صافکاری بدون رنگ',
            'fender_paint' => 'گلگیر رنگ',
            'partial_paint' => 'دور رنگ',
            'full_paint' => 'کامل رنگ',
        ];
    }

    public static function label(array $map, ?string $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return $map[$value] ?? $value;
    }
}
