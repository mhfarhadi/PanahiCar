<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class InstallmentController extends Controller
{
    public function markPaid(int $installment): RedirectResponse
    {
        DB::transaction(function () use ($installment) {
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
                    'paid_at' => now()->toDateString(),
                    'updated_at' => now(),
                ]);
        });

        return back()->with('success', 'پاس شدن چک با موفقیت ثبت شد.');
    }
}
