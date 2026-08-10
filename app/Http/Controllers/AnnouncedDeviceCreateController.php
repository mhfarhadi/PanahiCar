<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Device;
use App\Models\DeviceImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncedDeviceCreateController extends Controller
{
    public function create(): Response
    {
        $brands = DB::table('brands')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $models = DB::table('device_models')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $storages = DB::table('storage_options')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $colors = DB::table('color_options')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $partNumbers = DB::table('part_number_options')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('AnnouncedDevices/Create', [
            'catalog' => [
                'brands' => $brands,
                'models' => $models,
                'storages' => $storages,
                'colors' => $colors,
                'modelStorages' => DB::table('device_model_storage_option')->get(),
                'modelColors' => DB::table('device_model_color_option')->get(),
                'partNumbers' => $partNumbers,
                'modelPartNumbers' => DB::table('device_model_part_number_option')->get(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:150'],
            'storage' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:100'],
            'part_number' => ['nullable', 'string', 'max:100'],
            'sim_type' => ['nullable', 'in:single,dual'],
            'battery_health' => ['nullable', 'integer', 'min:0', 'max:100'],
            'condition_grade' => ['nullable', 'string', 'max:50'],
            'imei' => ['nullable', 'digits:15', 'unique:devices,imei'],
            'registration_status' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],

            'announcer_name' => ['required', 'string', 'max:150'],
            'announcer_mobile' => ['nullable', 'string', 'max:20'],
            'announced_price' => ['nullable', 'integer', 'min:0'],
            'announced_at' => ['required', 'date'],

            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'max:5120'],
        ]);

        if (!empty($validated['announcer_mobile'])) {
            $validated['announcer_mobile'] = strtr($validated['announcer_mobile'], [
                '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
                '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
                '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
                '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            ]);
        }

        DB::transaction(function () use ($request, $validated) {
            $announcer = new Contact();
            $announcer->name = $validated['announcer_name'];
            $announcer->mobile = $validated['announcer_mobile'] ?? null;
            $announcer->created_by = $request->user()->id;
            $announcer->save();

            $device = new Device();
            $device->brand = $validated['brand'];
            $device->model = $validated['model'];
            $device->storage = $validated['storage'] ?? null;
            $device->color = $validated['color'] ?? null;
            $device->part_number = $validated['part_number'] ?? null;
            $device->sim_type = $validated['sim_type'] ?? null;
            $device->battery_health = $validated['battery_health'] ?? null;
            $device->condition_grade = $validated['condition_grade'] ?? null;
            $device->imei = $validated['imei'] ?? null;
            $device->registration_status = $validated['registration_status'] ?? null;
            $device->description = $validated['description'] ?? null;

            $device->status = 'announced';
            $device->announced_by_id = $announcer->id;
            $device->announced_price = $validated['announced_price'] ?? null;
            $device->announced_at = $validated['announced_at'];
            $device->created_by = $request->user()->id;
            $device->save();

            foreach ($request->file('images', []) as $index => $image) {
                $deviceImage = new DeviceImage();
                $deviceImage->device_id = $device->id;
                $deviceImage->image_path = $image->store('devices', 'public');
                $deviceImage->is_cover = $index === 0;
                $deviceImage->sort_order = $index;
                $deviceImage->save();
            }
        });

        return redirect()
            ->route('announced-devices.index')
            ->with('success', 'گوشی اعلامی با موفقیت ثبت شد.');
    }
}
