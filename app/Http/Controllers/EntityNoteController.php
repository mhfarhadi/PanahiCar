<?php

namespace App\Http\Controllers;

use App\Services\EntityNoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EntityNoteController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'entity_type' => ['required', 'in:contact,device,purchase,sale,installment'],
            'entity_id' => ['required', 'integer', 'min:1'],
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $table = match ($validated['entity_type']) {
            'contact' => 'contacts',
            'device' => 'devices',
            'purchase' => 'purchases',
            'sale' => 'sales',
            'installment' => 'installments',
        };

        $exists = DB::table($table)
            ->where('id', $validated['entity_id'])
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'entity_id' => 'رکورد موردنظر پیدا نشد.',
            ]);
        }

        EntityNoteService::add(
            $validated['entity_type'],
            $validated['entity_id'],
            $validated['body'],
            $request->user()->id
        );

        return back()->with('success', 'یادداشت جدید ثبت شد.');
    }
}
