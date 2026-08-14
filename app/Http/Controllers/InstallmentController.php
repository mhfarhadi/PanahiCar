<?php

namespace App\Http\Controllers;

use App\Services\EntityNoteService;
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

        $search = $this->normalizeDigits($search);

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
                        ->orWhere('d.imei', 'like', "%{$search}%")
                        ->orWhere('i.check_number', 'like', "%{$search}%")
                        ->orWhere('i.bank_name', 'like', "%{$search}%")
                        ->orWhere('i.sayad_id', 'like', "%{$search}%");
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
                'i.check_number',
                'i.bank_name',
                'i.sayad_id',
                's.sale_date',
                'c.id as buyer_id',
                'c.name as buyer_name',
                'c.mobile as buyer_mobile',
                'd.brand',
                'd.model',
                'd.storage',
                'd.imei',
            ]);

        $installmentIds = $installments->pluck('id');

        $imagesByInstallment = $installmentIds->isEmpty()
            ? collect()
            : DB::table('installment_images')
                ->whereIn('installment_id', $installmentIds)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get([
                    'id',
                    'installment_id',
                    'image_path',
                    'sort_order',
                    'created_at',
                ])
                ->groupBy('installment_id');

        $notesByInstallment = $installmentIds->isEmpty()
            ? collect()
            : DB::table('entity_notes as n')
                ->leftJoin('users as u', 'u.id', '=', 'n.created_by')
                ->where('n.entity_type', 'installment')
                ->whereIn('n.entity_id', $installmentIds)
                ->orderByDesc('n.created_at')
                ->orderByDesc('n.id')
                ->get([
                    'n.id',
                    'n.entity_id',
                    'n.body',
                    'n.created_at',
                    'u.name as author_name',
                ])
                ->groupBy('entity_id');

        $installments = $installments
            ->map(function ($installment) use (
                $today,
                $weekAhead,
                $imagesByInstallment,
                $notesByInstallment
            ) {
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

                $installment->images = ($imagesByInstallment[$installment->id] ?? collect())
                    ->values();

                $installment->notes = ($notesByInstallment[$installment->id] ?? collect())
                    ->values();

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

    public function updateCheckDetails(
        Request $request,
        int $installment
    ): RedirectResponse {
        $request->merge([
            'check_number' => $this->nullableNormalizedDigits(
                $request->input('check_number')
            ),
            'sayad_id' => $this->nullableNormalizedDigits(
                $request->input('sayad_id')
            ),
        ]);

        $validated = $request->validate([
            'check_number' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'sayad_id' => ['nullable', 'digits:16'],
            'images' => ['nullable', 'array', 'max:6'],
            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'note' => ['nullable', 'string', 'max:10000'],
        ]);

        $row = DB::table('installments')
            ->where('id', $installment)
            ->first();

        abort_unless($row, 404);

        DB::table('installments')
            ->where('id', $installment)
            ->update([
                'check_number' => $validated['check_number'] ?: null,
                'bank_name' => trim((string) ($validated['bank_name'] ?? '')) ?: null,
                'sayad_id' => $validated['sayad_id'] ?: null,
                'updated_at' => now(),
            ]);

        if ($request->hasFile('images')) {
            $sortOrder = (int) (
                DB::table('installment_images')
                    ->where('installment_id', $installment)
                    ->max('sort_order') ?? -1
            );

            foreach ($request->file('images') as $image) {
                $sortOrder++;

                $path = $image->store(
                    "installment-checks/{$installment}",
                    'public'
                );

                DB::table('installment_images')->insert([
                    'installment_id' => $installment,
                    'image_path' => $path,
                    'sort_order' => $sortOrder,
                    'uploaded_by' => $request->user()->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        EntityNoteService::add(
            'installment',
            $installment,
            $validated['note'] ?? null,
            $request->user()->id
        );

        return back()->with(
            'success',
            'اطلاعات چک با موفقیت ذخیره شد.'
        );
    }

    public function markPaid(Request $request, int $installment): RedirectResponse
    {
        $validated = $request->validate([
            'paid_at' => ['required', 'date'],
        ]);

        $userId = $request->user()->id;

        DB::transaction(function () use ($installment, $validated, $userId) {
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

            EntityNoteService::add(
                'installment',
                $installment,
                sprintf(
                    'وصول چک ثبت شد. تاریخ پاس شدن: %s، مبلغ وصول: %s تومان.',
                    $validated['paid_at'],
                    number_format((int) $row->amount)
                ),
                $userId
            );
        });

        return back()->with('success', 'پاس شدن چک با موفقیت ثبت شد.');
    }

    public function reversePaid(Request $request, int $installment): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $userId = $request->user()->id;

        DB::transaction(function () use ($installment, $validated, $userId) {
            $row = DB::table('installments')
                ->where('id', $installment)
                ->lockForUpdate()
                ->first();

            abort_unless($row, 404);

            if ($row->status !== 'paid') {
                return;
            }

            $previousPaidAt = $row->paid_at;
            $previousPaidAmount = (int) $row->paid_amount;

            DB::table('installments')
                ->where('id', $installment)
                ->update([
                    'paid_amount' => 0,
                    'status' => 'pending',
                    'paid_at' => null,
                    'updated_at' => now(),
                ]);

            EntityNoteService::add(
                'installment',
                $installment,
                sprintf(
                    'اصلاح وصول چک: ثبت پاس شدن لغو شد. تاریخ وصول قبلی: %s، مبلغ وصول قبلی: %s تومان. دلیل اصلاح: %s',
                    $previousPaidAt ?: 'نامشخص',
                    number_format($previousPaidAmount),
                    trim($validated['reason'])
                ),
                $userId
            );
        });

        return back()->with(
            'success',
            'ثبت پاس شدن چک با حفظ سابقه اصلاح شد.'
        );
    }

    private function normalizeDigits(?string $value): string
    {
        return strtr((string) $value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }

    private function nullableNormalizedDigits(mixed $value): ?string
    {
        $normalized = trim($this->normalizeDigits((string) $value));

        return $normalized === '' ? null : $normalized;
    }
}
