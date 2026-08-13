<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InstallmentController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status', 'open');

        $search = strtr($search, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        if (! in_array($status, ['all', 'open', 'overdue', 'due_soon', 'paid'], true)) {
            $status = 'open';
        }

        $today = now()->toDateString();
        $weekAhead = now()->addDays(7)->toDateString();

        $baseQuery = DB::table('installments as i')
            ->join('sales as s', 's.id', '=', 'i.sale_id')
            ->join('contacts as c', 'c.id', '=', 's.buyer_id')
            ->join('devices as d', 'd.id', '=', 's.device_id')
            ->where('s.sale_type', 'installment')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('c.name', 'like', "%{$search}%")
                        ->orWhere('c.mobile', 'like', "%{$search}%")
                        ->orWhere('d.brand', 'like', "%{$search}%")
                        ->orWhere('d.model', 'like', "%{$search}%")
                        ->orWhere('d.storage', 'like', "%{$search}%")
                        ->orWhere('d.imei', 'like', "%{$search}%");
                });
            });

        $summaryQuery = clone $baseQuery;

        $summary = [
            'open_count' => (clone $summaryQuery)
                ->where('i.status', '!=', 'paid')
                ->count(),

            'open_amount' => (int) ((clone $summaryQuery)
                ->where('i.status', '!=', 'paid')
                ->selectRaw('COALESCE(SUM(i.amount - i.paid_amount), 0) as total')
                ->value('total') ?? 0),

            'overdue_count' => (clone $summaryQuery)
                ->where('i.status', '!=', 'paid')
                ->where('i.due_date', '<', $today)
                ->count(),

            'overdue_amount' => (int) ((clone $summaryQuery)
                ->where('i.status', '!=', 'paid')
                ->where('i.due_date', '<', $today)
                ->selectRaw('COALESCE(SUM(i.amount - i.paid_amount), 0) as total')
                ->value('total') ?? 0),

            'due_soon_count' => (clone $summaryQuery)
                ->where('i.status', '!=', 'paid')
                ->whereBetween('i.due_date', [$today, $weekAhead])
                ->count(),

            'due_soon_amount' => (int) ((clone $summaryQuery)
                ->where('i.status', '!=', 'paid')
                ->whereBetween('i.due_date', [$today, $weekAhead])
                ->selectRaw('COALESCE(SUM(i.amount - i.paid_amount), 0) as total')
                ->value('total') ?? 0),

            'paid_count' => (clone $summaryQuery)
                ->where('i.status', 'paid')
                ->count(),

            'paid_amount' => (int) ((clone $summaryQuery)
                ->where('i.status', 'paid')
                ->selectRaw('COALESCE(SUM(i.paid_amount), 0) as total')
                ->value('total') ?? 0),
        ];

        $query = clone $baseQuery;

        if ($status === 'open') {
            $query->where('i.status', '!=', 'paid');
        }

        if ($status === 'overdue') {
            $query
                ->where('i.status', '!=', 'paid')
                ->where('i.due_date', '<', $today);
        }

        if ($status === 'due_soon') {
            $query
                ->where('i.status', '!=', 'paid')
                ->whereBetween('i.due_date', [$today, $weekAhead]);
        }

        if ($status === 'paid') {
            $query->where('i.status', 'paid');
        }

        $installments = $query
            ->orderByRaw("CASE WHEN i.status = 'paid' THEN 1 ELSE 0 END")
            ->orderBy('i.due_date')
            ->orderBy('i.id')
            ->get([
                'i.id',
                'i.sale_id',
                'i.installment_number',
                'i.due_date',
                'i.amount',
                'i.paid_amount',
                'i.status',
                'i.paid_at',
                'i.notes',
                's.sale_date',
                'c.id as buyer_id',
                'c.name as buyer_name',
                'c.mobile as buyer_mobile',
                'd.brand',
                'd.model',
                'd.storage',
                'd.imei',
            ])
            ->map(function ($installment) use ($today, $weekAhead) {
                $installment->remaining_amount = max(
                    0,
                    (int) $installment->amount - (int) $installment->paid_amount
                );

                $installment->is_overdue =
                    $installment->status !== 'paid'
                    && $installment->due_date < $today;

                $installment->is_due_soon =
                    $installment->status !== 'paid'
                    && $installment->due_date >= $today
                    && $installment->due_date <= $weekAhead;

                return $installment;
            });

        return Inertia::render('Installments/Index', [
            'installments' => $installments,
            'summary' => $summary,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function markPaid(Request $request, int $installment): RedirectResponse
    {
        $validated = $request->validate([
            'paid_at' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($installment, $validated) {
            $row = DB::table('installments')
                ->where('id', $installment)
                ->lockForUpdate()
                ->first();

            abort_unless($row, 404);

            if ($row->status === 'paid') {
                return;
            }

            DB::table('installments')
                ->where('id', $installment)
                ->update([
                    'paid_amount' => $row->amount,
                    'status' => 'paid',
                    'paid_at' => $validated['paid_at'],
                    'updated_at' => now(),
                ]);
        });

        return back()->with('success', 'پاس شدن چک با موفقیت ثبت شد.');
    }
}
