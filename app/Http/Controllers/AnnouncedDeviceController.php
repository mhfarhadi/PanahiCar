<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncedDeviceController extends Controller
{
    public function index(): Response
    {
        $devices = DB::table('devices as d')
            ->leftJoin('contacts as c', 'c.id', '=', 'd.announced_by_id')
            ->where('d.status', 'announced')
            ->orderByDesc('d.id')
            ->select([
                'd.id',
                'd.brand',
                'd.model',
                'd.storage',
                'd.color',
                'd.part_number',
                'd.sim_type',
                'd.battery_health',
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
        ]);
    }
}
