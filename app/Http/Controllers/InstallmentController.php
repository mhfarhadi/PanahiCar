<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstallmentController extends Controller
{
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
