<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Services\EntityNoteService;
use App\Services\ContactManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search'));
        $type = trim((string) $request->query('type'));
        $view = $request->query('view') === 'archived'
            ? 'archived'
            : 'active';

        if (! in_array($type, ['colleague', 'individual'], true)) {
            $type = '';
        }

        $contacts = Contact::query()
            ->when(
                $view === 'archived',
                fn ($query) => $query->whereNotNull('archived_at'),
                fn ($query) => $query->whereNull('archived_at')
            )
            ->when($type !== '', function ($query) use ($type) {
                $query->where('contact_type', $type);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'mobile',
                'phone',
                'description',
                'avatar_path',
                'contact_type',
                'archived_at',
                'created_at',
            ]);

        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts,
            'filters' => [
                'search' => $search,
                'type' => $type,
                'view' => $view,
            ],
        ]);
    }


    public function create(Request $request): Response
{
    $returnTo = $request->query('return_to');

    if (! in_array($returnTo, ['devices.create', 'announced-devices.create'], true)) {
        $returnTo = null;
    }

    return Inertia::render('Contacts/Create', [
        'returnTo' => $returnTo,
        'defaultContactType' => $returnTo === 'announced-devices.create'
            ? 'colleague'
            : 'individual',
    ]);
}

public function store(Request $request): RedirectResponse
{
    $request->merge([
        'mobile' => $this->normalizeDigits($request->mobile),
        'phone' => $this->normalizeDigits($request->phone),
    ]);

    if ($request->input('return_to') === 'announced-devices.create') {
        $request->merge([
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'contact_type' => 'colleague',
        ]);
    }

    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'mobile' => ['required', 'string', 'max:20', 'unique:contacts,mobile'],
        'phone' => ['nullable', 'string', 'max:20'],
        'description' => ['nullable', 'string'],
        'contact_type' => ['required', 'in:colleague,individual'],
        'return_to' => ['nullable', 'in:devices.create,announced-devices.create'],
    ]);

    $contact = new Contact();
    $contact->name = $validated['name'];
    $contact->mobile = $validated['mobile'];
    $contact->phone = $validated['phone'] ?? null;
    $contact->description = $validated['description'] ?? null;
    $contact->contact_type = $validated['contact_type'];
    $contact->created_by = $request->user()->id;

    if ($request->hasFile('avatar')) {
        $contact->avatar_path = $request->file('avatar')->store('contacts', 'public');
    }
    $contact->save();

    EntityNoteService::add(
        'contact',
        $contact->id,
        $validated['description'] ?? null,
        $request->user()->id
    );

    if (! empty($validated['return_to'])) {
        return redirect()
            ->route($validated['return_to'], [
                'created_contact' => $contact->id,
            ])
            ->with('success', 'شخص جدید با موفقیت ثبت شد.');
    }

    return redirect()
        ->route('contacts.index')
        ->with('success', 'شخص جدید با موفقیت ثبت شد.');
}

    public function edit(Request $request, Contact $contact): Response
    {
        abort_unless(
            ContactManagementService::canManage($request->user(), $contact),
            403
        );

        return Inertia::render('Contacts/Edit', [
            'contact' => [
                'id' => $contact->id,
                'name' => $contact->name,
                'mobile' => $contact->mobile,
                'phone' => $contact->phone,
                'contact_type' => $contact->contact_type,
                'avatar_path' => $contact->avatar_path,
                'archived_at' => $contact->archived_at,
            ],
            'canChangeType' => $request->user()->role === 'super_admin',
        ]);
    }

    public function update(Request $request, Contact $contact): RedirectResponse
    {
        abort_unless(
            ContactManagementService::canManage($request->user(), $contact),
            403
        );

        $request->merge([
            'mobile' => $this->normalizeDigits($request->mobile),
            'phone' => $this->normalizeDigits($request->phone),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => [
                'required',
                'string',
                'max:20',
                Rule::unique('contacts', 'mobile')->ignore($contact->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'contact_type' => ['required', 'in:colleague,individual'],
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        if (
            $request->user()->role !== 'super_admin'
            && $validated['contact_type'] !== $contact->contact_type
        ) {
            abort(403);
        }

        $contact->name = $validated['name'];
        $contact->mobile = $validated['mobile'];
        $contact->phone = $validated['phone'] ?? null;
        $contact->contact_type = $validated['contact_type'];

        if ($request->boolean('remove_avatar') && $contact->avatar_path) {
            Storage::disk('public')->delete($contact->avatar_path);
            $contact->avatar_path = null;
        }

        if ($request->hasFile('avatar')) {
            if ($contact->avatar_path) {
                Storage::disk('public')->delete($contact->avatar_path);
            }

            $contact->avatar_path = $request
                ->file('avatar')
                ->store('contacts', 'public');
        }

        $contact->save();

        return redirect()
            ->route('contacts.show', $contact)
            ->with('success', 'اطلاعات شخص با موفقیت ویرایش شد.');
    }

    public function archive(Request $request, Contact $contact): RedirectResponse
    {
        abort_unless(
            ContactManagementService::canManage($request->user(), $contact),
            403
        );

        $contact->archived_at ??= now();
        $contact->save();

        return redirect()
            ->route('contacts.index')
            ->with('success', 'شخص آرشیو شد و سوابق او حفظ شد.');
    }

    public function restore(Request $request, Contact $contact): RedirectResponse
    {
        abort_unless(
            ContactManagementService::canManage($request->user(), $contact),
            403
        );

        $contact->archived_at = null;
        $contact->save();

        return redirect()
            ->route('contacts.show', $contact)
            ->with('success', 'شخص از آرشیو خارج شد.');
    }

    public function destroy(Request $request, Contact $contact): RedirectResponse
    {
        abort_unless(
            ContactManagementService::canManage($request->user(), $contact),
            403
        );

        if (ContactManagementService::hasHistory($contact)) {
            $contact->archived_at ??= now();
            $contact->save();

            return redirect()
                ->route('contacts.index')
                ->with(
                    'success',
                    'این شخص سابقه دارد؛ برای حفظ سوابق به‌جای حذف، آرشیو شد.'
                );
        }

        DB::transaction(function () use ($contact) {
            DB::table('entity_notes')
                ->where('entity_type', 'contact')
                ->where('entity_id', $contact->id)
                ->delete();

            if ($contact->avatar_path) {
                Storage::disk('public')->delete($contact->avatar_path);
            }

            $contact->delete();
        });

        return redirect()
            ->route('contacts.index')
            ->with('success', 'شخص بدون سابقه با موفقیت حذف شد.');
    }



private function normalizeDigits(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return strtr($value, [
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
        ]);
    }
}
