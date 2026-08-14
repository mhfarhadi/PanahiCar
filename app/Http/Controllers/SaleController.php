<?php

namespace App\Http\Controllers;
use Illuminate\Validation\ValidationException;
use Morilog\Jalali\Jalalian;

use App\Models\Device;
use App\Models\Sale;
use App\Services\EntityNoteService;
use App\Services\CurrencyRateService;
use App\Services\InstallmentCalculatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SaleController extends Controller
{

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search'));
        $saleType = (string) $request->query('sale_type', 'all');
        $period = (string) $request->query('period', 'all');

        $search = strtr($search, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        if (! in_array($saleType, ['all', 'cash', 'installment'], true)) {
            $saleType = 'all';
        }

        if (! in_array($period, ['all', 'last_7_days', 'current_month', 'previous_month'], true)) {
            $period = 'all';
        }

        $query = DB::table('sales as s')
            ->join('devices as d', 'd.id', '=', 's.device_id')
            ->join('contacts as c', 'c.id', '=', 's.buyer_id')
            ->leftJoin('purchases as p', 'p.device_id', '=', 'd.id')
            ->where('d.status', 'sold')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('d.brand', 'like', "%{$search}%")
                        ->orWhere('d.model', 'like', "%{$search}%")
                        ->orWhere('d.storage', 'like', "%{$search}%")
                        ->orWhere('d.color', 'like', "%{$search}%")
                        ->orWhere('d.imei', 'like', "%{$search}%")
                        ->orWhere('c.name', 'like', "%{$search}%")
                        ->orWhere('c.mobile', 'like', "%{$search}%");
                });
            })
            ->when($saleType !== 'all', function ($query) use ($saleType) {
                $query->where('s.sale_type', $saleType);
            });

        $today = now()->toDateString();

        if ($period === 'last_7_days') {
            $query->whereBetween('s.sale_date', [
                now()->subDays(6)->toDateString(),
                $today,
            ]);
        }

        if (in_array($period, ['current_month', 'previous_month'], true)) {
            $jalali = Jalalian::now();

            if ($period === 'previous_month') {
                $jalali = $jalali->subMonths(1);
            }

            $month = $jalali->getMonth();
            $year = $jalali->getYear();

            $monthStart = Jalalian::fromFormat(
                'Y/m/d',
                sprintf('%04d/%02d/01', $year, $month)
            )->toCarbon()->toDateString();

            $monthEndDay = $month <= 6
                ? 31
                : ($month === 12
                    ? ($jalali->isLeapYear() ? 30 : 29)
                    : 30);

            $monthEnd = Jalalian::fromFormat(
                'Y/m/d',
                sprintf('%04d/%02d/%02d', $year, $month, $monthEndDay)
            )->toCarbon()->toDateString();

            $query->whereBetween('s.sale_date', [$monthStart, $monthEnd]);
        }

        $summaryQuery = clone $query;

        $summary = [
            'count' => (clone $summaryQuery)->count('s.id'),
            'total_sale_amount' => (int) ((clone $summaryQuery)->sum('s.sale_price') ?? 0),
        ];

        $sales = $query
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
            'summary' => $summary,
            'filters' => [
                'search' => $search,
                'sale_type' => $saleType,
                'period' => $period,
            ],
        ]);
    }


    public function show(Sale $sale, CurrencyRateService $currencyRateService): Response
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
            's.usd_rate',
            's.usd_rate_date',
            's.usd_rate_source',
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
                'check_number',
                'bank_name',
                'sayad_id',
                'notes',
            ]);

        $installmentIds = $installments->pluck('id');

        $imagesByInstallment = $installmentIds->isEmpty()
            ? collect()
            : DB::table('installment_images')
                ->whereIn('installment_id', $installmentIds)
                ->whereNull('removed_at')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get([
                    'id',
                    'installment_id',
                    'image_path',
                    'sort_order',
                ])
                ->groupBy('installment_id');

        $installments = $installments->map(function ($installment) use ($imagesByInstallment) {
            $installment->images = ($imagesByInstallment[$installment->id] ?? collect())
                ->values();

            return $installment;
        });

        $currentRates = $currencyRateService->latest();

    return Inertia::render('Sales/Show', [
            'sale' => $saleData,
            'installments' => $installments,
        'currentUsdRate' => (int) ($currentRates['usd']['value'] ?? 0),
        ]);
    }


    public function create(Device $device): Response
    {
        abort_unless($device->status === 'in_stock', 404);

        $contacts = DB::table('contacts')
            ->whereNull('archived_at')
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

    public function currencyRate(Request $request, CurrencyRateService $currencyRateService)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $snapshot = $currencyRateService->snapshotForDate(
            'USD',
            $validated['date']
        );

        if (! $snapshot) {
            return response()->json([
                'found' => false,
                'rate' => null,
                'rate_date' => $validated['date'],
                'source' => null,
            ]);
        }

        return response()->json([
            'found' => true,
            'rate' => $snapshot['rate'],
            'rate_date' => $snapshot['rate_date'],
            'source' => $snapshot['source'],
        ]);
    }


    public function store(
        Request $request,
        Device $device,
        CurrencyRateService $currencyRateService,
        InstallmentCalculatorService $installmentCalculatorService
    ): RedirectResponse
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
            'usd_rate' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        $currencySnapshot = $currencyRateService->snapshotForDate(
            'USD',
            $validated['sale_date']
        );

        if (! $currencySnapshot && ! empty($validated['usd_rate'])) {
            $currencySnapshot = [
                'rate' => (int) $validated['usd_rate'],
                'rate_date' => $validated['sale_date'],
                'source' => 'manual',
            ];
        }
        $installmentCalculation = null;

        if (($validated['sale_type'] ?? null) === 'installment') {
            $deferment = $installmentCalculatorService->calculateDeferment(
                $validated['sale_date'],
                $validated['first_due_date']
            );

            if ($deferment['is_before_standard']) {
                throw ValidationException::withMessages([
                    'first_due_date' => 'اولین سررسید نمی‌تواند قبل از یک ماه شمسی پس از تاریخ فروش باشد.',
                ]);
            }

            $installmentCalculation = $installmentCalculatorService->calculate(
                salePrice: (int) $validated['sale_price'],
                downPayment: (int) $validated['down_payment'],
                monthlyProfitRate: (float) $validated['monthly_profit_rate'],
                installmentCount: (int) $validated['installment_count'],
                saleDate: $validated['sale_date'],
                firstDueDate: $validated['first_due_date'],
            );
        }

        DB::transaction(function () use ($request, $device, $validated, $installmentCalculation, $currencySnapshot) {
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
                $principal = $installmentCalculation['principal'];
                $monthlyProfitRate = (float) $installmentCalculation['monthly_profit_rate'];
                $installmentProfit = $installmentCalculation['installment_profit'];
                $installmentTotal = $installmentCalculation['installment_total'];
                $defermentMonths = $installmentCalculation['deferment_months'];
                $defermentDays = $installmentCalculation['deferment_days'];
                $defermentProfit = $installmentCalculation['deferment_profit'];
                $standardFirstDueDate = $installmentCalculation['standard_first_due_date'];
                $firstDueDate = $installmentCalculation['first_due_date'];
                $installmentAmount = $installmentCalculation['installment_amount'];
                $contractTotal = $installmentCalculation['contract_total'];
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
        $sale->usd_rate = $currencySnapshot['rate'] ?? null;
        $sale->usd_rate_date = $currencySnapshot['rate_date'] ?? null;
        $sale->usd_rate_source = $currencySnapshot['source'] ?? null;
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
                $timestamp = now();
                $rows = [];

                foreach ($installmentCalculation['installments'] as $installment) {
                    $rows[] = [
                        'sale_id' => $sale->id,
                        'installment_number' => $installment['installment_number'],
                        'due_date' => $installment['due_date'],
                        'amount' => $installment['amount'],
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
