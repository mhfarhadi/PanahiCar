<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SaleController extends Controller
{

    public function index(): Response
    {
        $sales = DB::table('sales as s')
            ->join('devices as d', 'd.id', '=', 's.device_id')
            ->join('contacts as c', 'c.id', '=', 's.buyer_id')
            ->leftJoin('purchases as p', 'p.device_id', '=', 'd.id')
            ->where('d.status', 'sold')
            ->orderByDesc('s.sale_date')
            ->orderByDesc('s.id')
            ->get([
                's.id',
                's.device_id',
                's.sale_type',
                's.sale_price',
                's.down_payment',
                's.sale_date',
                's.notes',
                'd.brand',
                'd.model',
                'd.storage',
                'd.color',
                'd.imei',
                'd.status',
                'c.id as buyer_id',
                'c.name as buyer_name',
                'c.mobile as buyer_mobile',
                'p.purchase_price',
            ])
            ->map(function ($sale) {
                $sale->profit = $sale->purchase_price !== null
                    ? $sale->sale_price - $sale->purchase_price
                    : null;

                $sale->cover_image = DB::table('device_images')
                    ->where('device_id', $sale->device_id)
                    ->orderByDesc('is_cover')
                    ->orderBy('sort_order')
                    ->value('image_path');

                return $sale;
            });

        return Inertia::render('Sales/Index', [
            'sales' => $sales,
        ]);
    }


    public function create(Device $device): Response
    {
        abort_unless($device->status === 'in_stock', 404);

        $contacts = DB::table('contacts')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'mobile',
                'contact_type',
            ]);

        $purchase = DB::table('purchases')
            ->where('device_id', $device->id)
            ->latest('id')
            ->first();

        return Inertia::render('Sales/Create', [
            'device' => [
                'id' => $device->id,
                'brand' => $device->brand,
                'model' => $device->model,
                'storage' => $device->storage,
                'color' => $device->color,
                'imei' => $device->imei,
                'purchase_price' => $purchase?->purchase_price,
            ],
            'contacts' => $contacts,
        ]);
    }

    public function store(Request $request, Device $device): RedirectResponse
    {
        abort_unless($device->status === 'in_stock', 404);

        $validated = $request->validate([
            'buyer_id' => ['required', 'exists:contacts,id'],
            'sale_price' => ['required', 'integer', 'min:0'],
            'sale_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $device, $validated) {
            $lockedDevice = Device::query()
                ->whereKey($device->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($lockedDevice->status === 'in_stock', 409);

            $sale = new Sale();
            $sale->device_id = $lockedDevice->id;
            $sale->buyer_id = $validated['buyer_id'];
            $sale->sale_type = 'cash';
            $sale->sale_price = $validated['sale_price'];
            $sale->down_payment = $validated['sale_price'];
            $sale->sale_date = $validated['sale_date'];
            $sale->notes = $validated['notes'] ?? null;
            $sale->created_by = $request->user()->id;
            $sale->save();

            $lockedDevice->status = 'sold';
            $lockedDevice->save();
        });

        return redirect()
            ->route('devices.index')
            ->with('success', 'فروش گوشی با موفقیت ثبت شد.');
    }
}
