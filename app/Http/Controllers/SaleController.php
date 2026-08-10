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
            'sale_type' => ['required', 'in:cash,installment'],
            'sale_price' => ['required', 'integer', 'min:0'],

            'down_payment' => [
                'exclude_unless:sale_type,installment',
                'required',
                'integer',
                'min:0',
                'lte:sale_price',
            ],

            'monthly_profit_rate' => [
                'exclude_unless:sale_type,installment',
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],

            'installment_count' => [
                'exclude_unless:sale_type,installment',
                'required',
                'integer',
                'min:1',
                'max:60',
            ],

            'first_due_date' => [
                'exclude_unless:sale_type,installment',
                'required',
                'date',
                'after_or_equal:sale_date',
            ],

            'sale_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $device, $validated) {
            $lockedDevice = Device::query()
                ->whereKey($device->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($lockedDevice->status === 'in_stock', 409);

            $isInstallment = $validated['sale_type'] === 'installment';

            $principal = 0;
            $installmentProfit = 0;
            $installmentTotal = 0;
            $monthlyProfitRate = null;
            $contractTotal = $validated['sale_price'];

            if ($isInstallment) {
                $principal = $validated['sale_price'] - $validated['down_payment'];
                $monthlyProfitRate = (float) $validated['monthly_profit_rate'];
                $count = $validated['installment_count'];

                $installmentProfit = (int) round(
                    $principal * ($monthlyProfitRate / 100) * $count
                );

                $installmentTotal = $principal + $installmentProfit;
                $contractTotal = $validated['down_payment'] + $installmentTotal;
            }

            $sale = new Sale();
            $sale->device_id = $lockedDevice->id;
            $sale->buyer_id = $validated['buyer_id'];
            $sale->sale_type = $validated['sale_type'];
            $sale->sale_price = $validated['sale_price'];
            $sale->down_payment = $isInstallment
                ? $validated['down_payment']
                : $validated['sale_price'];
            $sale->monthly_profit_rate = $monthlyProfitRate;
            $sale->installment_profit = $installmentProfit;
            $sale->contract_total = $contractTotal;
            $sale->sale_date = $validated['sale_date'];
            $sale->notes = $validated['notes'] ?? null;
            $sale->created_by = $request->user()->id;
            $sale->save();

            if ($isInstallment) {
                $count = $validated['installment_count'];

                $baseAmount = intdiv($installmentTotal, $count);
                $remainder = $installmentTotal % $count;

                $firstDueDate = \Carbon\Carbon::parse($validated['first_due_date']);
                $timestamp = now();

                $rows = [];

                for ($i = 0; $i < $count; $i++) {
                    $rows[] = [
                        'sale_id' => $sale->id,
                        'installment_number' => $i + 1,
                        'due_date' => $firstDueDate
                            ->copy()
                            ->addMonthsNoOverflow($i)
                            ->toDateString(),
                        'amount' => $baseAmount + ($i < $remainder ? 1 : 0),
                        'paid_amount' => 0,
                        'status' => 'pending',
                        'paid_at' => null,
                        'notes' => null,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }

                DB::table('installments')->insert($rows);
            }

            $lockedDevice->status = 'sold';
            $lockedDevice->save();
        });

        return redirect()
            ->route('sales.index')
            ->with('success', 'فروش گوشی با موفقیت ثبت شد.');
    }
}
