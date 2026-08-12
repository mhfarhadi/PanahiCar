<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Morilog\Jalali\Jalalian;

use App\Models\Device;
use App\Models\Sale;
use App\Services\EntityNoteService;
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



    public function show(Sale $sale): Response
    {
        $saleData = DB::table('sales as s')
            ->join('devices as d', 'd.id', '=', 's.device_id')
            ->join('contacts as c', 'c.id', '=', 's.buyer_id')
            ->leftJoin('purchases as p', 'p.device_id', '=', 'd.id')
            ->where('s.id', $sale->id)
            ->select([
                's.id',
                's.device_id',
                's.buyer_id',
                's.sale_type',
                's.sale_price',
                's.down_payment',
                's.monthly_profit_rate',
                's.installment_profit',
                's.contract_total',
                's.sale_date',
                's.notes',
                'd.brand',
                'd.model',
                'd.storage',
                'd.color',
                'd.imei',
                'd.status',
                'c.name as buyer_name',
                'c.mobile as buyer_mobile',
                'c.contact_type as buyer_contact_type',
                'p.purchase_price',
            ])
            ->first();

        abort_unless($saleData, 404);

        $saleData->trading_profit = $saleData->purchase_price !== null
            ? $saleData->sale_price - $saleData->purchase_price
            : null;

        $saleData->total_profit = $saleData->purchase_price !== null
            ? ($saleData->contract_total ?? $saleData->sale_price) - $saleData->purchase_price
            : null;

        $saleData->cover_image = DB::table('device_images')
            ->where('device_id', $saleData->device_id)
            ->orderByDesc('is_cover')
            ->orderBy('sort_order')
            ->value('image_path');

        $installments = DB::table('installments')
            ->where('sale_id', $sale->id)
            ->orderBy('installment_number')
            ->get([
                'id',
                'installment_number',
                'due_date',
                'amount',
                'paid_amount',
                'status',
                'paid_at',
                'notes',
            ]);

        return Inertia::render('Sales/Show', [
            'sale' => $saleData,
            'installments' => $installments,
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

        $deferment = null;

        if (($validated['sale_type'] ?? null) === 'installment') {
            $deferment = $this->calculateInstallmentDeferment(
                $validated['sale_date'],
                $validated['first_due_date']
            );

            if ($deferment['is_before_standard']) {
                throw ValidationException::withMessages([
                    'first_due_date' => 'اولین سررسید نمی‌تواند قبل از یک ماه شمسی پس از تاریخ فروش باشد.',
                ]);
            }
        }

        DB::transaction(function () use ($request, $device, $validated, $deferment) {
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
            $defermentProfit = 0;
            $defermentMonths = 0;
            $defermentDays = 0;
            $standardFirstDueDate = null;
            $firstDueDate = null;

            if ($isInstallment) {
                $principal = $validated['sale_price'] - $validated['down_payment'];
                $monthlyProfitRate = (float) $validated['monthly_profit_rate'];
                $count = $validated['installment_count'];

                $baseInstallmentProfit = (int) round(
                    $principal * ($monthlyProfitRate / 100) * $count
                );

                $defermentMonths = $deferment['months'];
                $defermentDays = $deferment['days'];
                $standardFirstDueDate = $deferment['standard_first_due_date'];
                $firstDueDate = $validated['first_due_date'];

                $defermentEquivalentMonths =
                    $defermentMonths + ($defermentDays / 30);

                $defermentProfit = (int) round(
                    $principal
                    * ($monthlyProfitRate / 100)
                    * $defermentEquivalentMonths
                );

                $installmentProfit =
                    $baseInstallmentProfit + $defermentProfit;

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
            $sale->standard_first_due_date = $standardFirstDueDate;
            $sale->first_due_date = $firstDueDate;
            $sale->deferment_months = $defermentMonths;
            $sale->deferment_days = $defermentDays;
            $sale->deferment_profit = $defermentProfit;
            $sale->sale_date = $validated['sale_date'];
            $sale->notes = $validated['notes'] ?? null;
            $sale->created_by = $request->user()->id;
            $sale->save();

            EntityNoteService::add(
                'sale',
                $sale->id,
                $validated['notes'] ?? null,
                $request->user()->id
            );

            if ($isInstallment) {
                $count = $validated['installment_count'];

                $baseAmount = intdiv($installmentTotal, $count);
                $remainder = $installmentTotal % $count;

                $firstDueJalali = Jalalian::fromCarbon(
                    Carbon::parse($validated['first_due_date'])
                );
                $timestamp = now();

                $rows = [];

                for ($i = 0; $i < $count; $i++) {
                    $rows[] = [
                        'sale_id' => $sale->id,
                        'installment_number' => $i + 1,
                        'due_date' => $firstDueJalali
                            ->addMonths($i)
                            ->toCarbon()
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

    private function calculateInstallmentDeferment(
        string $saleDate,
        string $firstDueDate
    ): array {
        $saleJalali = Jalalian::fromCarbon(
            Carbon::parse($saleDate)->startOfDay()
        );

        $standardJalali = $saleJalali->addMonths(1);
        $standardCarbon = $standardJalali->toCarbon()->startOfDay();
        $chosenCarbon = Carbon::parse($firstDueDate)->startOfDay();

        if ($chosenCarbon->lt($standardCarbon)) {
            return [
                'is_before_standard' => true,
                'standard_first_due_date' => $standardCarbon->toDateString(),
                'months' => 0,
                'days' => 0,
            ];
        }

        $months = 0;
        $cursorJalali = $standardJalali;

        while (true) {
            $nextJalali = $cursorJalali->addMonths(1);
            $nextCarbon = $nextJalali->toCarbon()->startOfDay();

            if ($nextCarbon->gt($chosenCarbon)) {
                break;
            }

            $months++;
            $cursorJalali = $nextJalali;
        }

        $cursorCarbon = $cursorJalali->toCarbon()->startOfDay();
        $days = (int) floor($cursorCarbon->diffInDays($chosenCarbon));

        return [
            'is_before_standard' => false,
            'standard_first_due_date' => $standardCarbon->toDateString(),
            'months' => $months,
            'days' => $days,
        ];
    }
}
