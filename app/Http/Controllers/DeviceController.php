<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceImage;
use App\Models\Purchase;
use App\Services\EntityNoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DeviceController extends Controller
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
            ->leftJoin('purchases as p', 'p.device_id', '=', 'd.id')
            ->leftJoin('contacts as c', 'c.id', '=', 'p.seller_id')
            ->where('d.status', 'in_stock')
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
                'd.sim_type',
                'd.battery_health',
                'd.condition_grade',
                'd.imei',
                'd.registration_status',
                'd.description',
                'p.purchase_price',
                'p.purchase_date',
                'c.name as seller_name',
                'c.mobile as seller_mobile',
            ])
            ->get()
            ->map(function ($device) {
                $device->suggested_sale_price = $device->purchase_price
                    ? (int) round($device->purchase_price * 1.10)
                    : null;

                $device->cover_image = DB::table('device_images')
                    ->where('device_id', $device->id)
                    ->orderByDesc('is_cover')
                    ->orderBy('sort_order')
                    ->value('image_path');

                return $device;
            });

        return Inertia::render('Devices/Index', [
            'devices' => $devices,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(Request $request): Response
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

        $contacts = DB::table('contacts')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'mobile',
            ]);

        return Inertia::render('Devices/Create', [
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

            'contacts' => $contacts,
            'createdContactId' => request()->integer('created_contact') ?: null,
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

            'seller_id' => ['required', 'integer', 'exists:contacts,id'],

            'purchase_price' => ['required', 'integer', 'min:0'],
            'purchase_date' => ['required', 'date'],

            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'max:5120'],
        ]);

        DB::transaction(function () use ($request, $validated) {
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
            $device->status = 'in_stock';
            $device->created_by = $request->user()->id;
            $device->save();

            EntityNoteService::add(
                'device',
                $device->id,
                $validated['description'] ?? null,
                $request->user()->id
            );

            $purchase = new Purchase();
            $purchase->device_id = $device->id;
            $purchase->seller_id = $validated['seller_id'];
            $purchase->purchase_price = $validated['purchase_price'];
            $purchase->purchase_date = $validated['purchase_date'];
            $purchase->created_by = $request->user()->id;
            $purchase->save();

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
            ->route('dashboard')
            ->with('success', 'دستگاه با موفقیت ثبت شد.');
    }
}
