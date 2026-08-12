<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncedDeviceController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search'));

        $search = strtr($search, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        $devices = DB::table('devices as d')
            ->leftJoin('contacts as c', 'c.id', '=', 'd.announced_by_id')
            ->where('d.status', 'announced')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('d.brand', 'like', "%{$search}%")
                        ->orWhere('d.model', 'like', "%{$search}%")
                        ->orWhere('d.storage', 'like', "%{$search}%")
                        ->orWhere('d.color', 'like', "%{$search}%")
                        ->orWhere('d.imei', 'like', "%{$search}%")
                        ->orWhere('d.part_number', 'like', "%{$search}%")
                        ->orWhere('c.name', 'like', "%{$search}%")
                        ->orWhere('c.mobile', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('d.id')
            ->select([
                'd.id',
                'd.brand',
                'd.model',
                'd.storage',
                'd.color',
                'd.part_number',
                'd.manufacturing_country',
                'd.sim_type',
                'd.battery_health',
                'd.battery_condition',
                'd.condition_grade',
                'd.imei',
                'd.registration_status',
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
        ]);
    }
}
