<?php

namespace App\Http\Controllers;

use App\Support\VehicleOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncedDeviceController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $this->normalizeDigits(trim((string) $request->query('search')));

        $devices = DB::table('devices as d')
            ->leftJoin('contacts as c', 'c.id', '=', 'd.announced_by_id')
            ->where('d.status', 'announced')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('d.brand', 'like', "%{$search}%")
                        ->orWhere('d.model', 'like', "%{$search}%")
                        ->orWhere('d.color', 'like', "%{$search}%")
                        ->orWhere('d.vin', 'like', "%{$search}%")
                        ->orWhere('c.name', 'like', "%{$search}%")
                        ->orWhere('c.mobile', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('d.id')
            ->select([
                'd.id',
                'd.brand',
                'd.model',
                'd.model_year',
                'd.mileage',
                'd.color',
                'd.transmission',
                'd.fuel_type',
                'd.body_condition',
                'd.vin',
                'd.description',
                'd.announced_price',
                'd.announced_at',
                'c.name as announcer_name',
                'c.mobile as announcer_mobile',
            ])
            ->get()
            ->map(function ($device) {
                $device->cover_image = DB::table('device_images')
                    ->where('device_id', $device->id)
                    ->orderByDesc('is_cover')
                    ->orderBy('sort_order')
                    ->value('image_path');

                return $device;
            });

        return Inertia::render('AnnouncedDevices/Index', [
            'devices' => $devices,
            'filters' => [
                'search' => $search,
            ],
            'optionLabels' => [
                'transmissions' => VehicleOptions::transmissions(),
                'fuelTypes' => VehicleOptions::fuelTypes(),
                'bodyConditions' => VehicleOptions::bodyConditions(),
            ],
        ]);
    }

    private function normalizeDigits(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }
}
