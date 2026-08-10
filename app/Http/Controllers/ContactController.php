<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search'));
        $type = trim((string) $request->query('type'));

        if (!in_array($type, ['colleague', 'individual'], true)) {
            $type = '';
        }

        $contacts = Contact::query()
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
                'contact_type',
                'created_at',
            ]);

        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts,
            'filters' => [
                'search' => $search,
                'type' => $type,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Contacts/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'mobile' => $this->normalizeDigits($request->mobile),
            'phone' => $this->normalizeDigits($request->phone),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20', 'unique:contacts,mobile'],
            'phone' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'contact_type' => ['required', 'in:colleague,individual'],
        ]);

        $contact = new Contact();
        $contact->name = $validated['name'];
        $contact->mobile = $validated['mobile'];
        $contact->phone = $validated['phone'] ?? null;
        $contact->description = $validated['description'] ?? null;
        $contact->contact_type = $validated['contact_type'];
        $contact->created_by = $request->user()->id;
        $contact->save();

        return redirect()
            ->route('contacts.index')
            ->with('success', 'شخص جدید با موفقیت ثبت شد.');
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
