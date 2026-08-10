<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Purchase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncedDevicePurchaseController extends Controller
{
    public function create(Device $device): Response
    {
        abort_unless($device->status === 'announced', 404);

        $announcer = DB::table('contacts')
            ->where('id', $device->announced_by_id)
            ->first();

        return Inertia::render('AnnouncedDevices/Purchase', [
            'device' => [
                'id' => $device->id,
                'brand' => $device->brand,
                'model' => $device->model,
                'storage' => $device->storage,
                'color' => $device->color,
                'imei' => $device->imei,
                'announced_price' => $device->announced_price,
                'announcer_name' => $announcer?->name,
                'announcer_mobile' => $announcer?->mobile,
            ],
        ]);
    }

    public function store(Request $request, Device $device): RedirectResponse
    {
        abort_unless($device->status === 'announced', 404);

        $validated = $request->validate([
            'purchase_price' => ['required', 'integer', 'min:0'],
            'purchase_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $device, $validated) {
            $purchase = new Purchase();
            $purchase->device_id = $device->id;
            $purchase->seller_id = $device->announced_by_id;
            $purchase->purchase_price = $validated['purchase_price'];
            $purchase->purchase_date = $validated['purchase_date'];
            $purchase->notes = $validated['notes'] ?? null;
            $purchase->created_by = $request->user()->id;
            $purchase->save();

            $device->status = 'in_stock';
            $device->save();
        });

        return redirect()
            ->route('devices.index')
            ->with('success', 'گوشی با موفقیت خریداری و به موجودی اضافه شد.');
    }
}
