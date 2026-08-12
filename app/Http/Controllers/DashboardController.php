<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Services\CurrencyRateService;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Morilog\Jalali\Jalalian;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(CurrencyRateService $currencyRateService): Response
    {
        $inventoryCount = Device::where('status', 'in_stock')->count();

        $today = now()->toDateString();
        $weekAhead = now()->addDays(7)->toDateString();

        $jalaliNow = Jalalian::now();

        $jalaliMonthStart = Jalalian::fromFormat(
            'Y/m/d',
            sprintf(
                '%04d/%02d/01',
                $jalaliNow->getYear(),
                $jalaliNow->getMonth()
            )
        )->toCarbon()->toDateString();

        $jalaliMonthEndDay = $jalaliNow->getMonth() <= 6
            ? 31
            : ($jalaliNow->getMonth() === 12
                ? ($jalaliNow->isLeapYear() ? 30 : 29)
                : 30);

        $jalaliMonthEnd = Jalalian::fromFormat(
            'Y/m/d',
            sprintf(
                '%04d/%02d/%02d',
                $jalaliNow->getYear(),
                $jalaliNow->getMonth(),
                $jalaliMonthEndDay
            )
        )->toCarbon()->toDateString();

        $salesThisMonth = DB::table('sales')
            ->whereBetween('sale_date', [$jalaliMonthStart, $jalaliMonthEnd])
            ->count();

        $openInstallments = DB::table('installments')
            ->where('status', 'pending');

        $receivables = [
            'total_count' => (clone $openInstallments)->count(),
            'total_amount' => (int) (clone $openInstallments)
                ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as total')
                ->value('total'),

            'overdue_count' => (clone $openInstallments)
                ->where('due_date', '<', $today)
                ->count(),
            'overdue_amount' => (int) (clone $openInstallments)
                ->where('due_date', '<', $today)
                ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as total')
                ->value('total'),

            'due_soon_count' => (clone $openInstallments)
                ->whereBetween('due_date', [$today, $weekAhead])
                ->count(),
            'due_soon_amount' => (int) (clone $openInstallments)
                ->whereBetween('due_date', [$today, $weekAhead])
                ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as total')
                ->value('total'),
        ];

        $upcomingInstallments = DB::table('installments as i')
            ->join('sales as s', 's.id', '=', 'i.sale_id')
            ->join('contacts as c', 'c.id', '=', 's.buyer_id')
            ->join('devices as d', 'd.id', '=', 's.device_id')
            ->where('i.status', 'pending')
            ->orderBy('i.due_date')
            ->orderBy('i.installment_number')
            ->limit(6)
            ->get([
                'i.id',
                'i.sale_id',
                'i.installment_number',
                'i.due_date',
                'i.amount',
                'i.paid_amount',
                'c.name as buyer_name',
                'd.brand',
                'd.model',
            ])
            ->map(function ($installment) use ($today) {
                $installment->remaining_amount = max(
                    0,
                    (int) $installment->amount - (int) $installment->paid_amount
                );

                $installment->is_overdue = $installment->due_date < $today;

                return $installment;
            });

        return Inertia::render('Dashboard', [
            'inventoryCount' => $inventoryCount,
            'salesThisMonth' => $salesThisMonth,
            'receivables' => $receivables,
            'upcomingInstallments' => $upcomingInstallments,
            'currencyRates' => $currencyRateService->latest(),
        ]);
    }

}
