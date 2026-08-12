<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ContactManagementService
{
    public static function canManage(User $user, Contact $contact): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        return $user->role === 'manager'
            && $contact->contact_type === 'individual';
    }

    public static function hasHistory(Contact $contact): bool
    {
        return DB::table('devices')
                ->where('announced_by_id', $contact->id)
                ->exists()
            || DB::table('purchases')
                ->where('seller_id', $contact->id)
                ->exists()
            || DB::table('sales')
                ->where('buyer_id', $contact->id)
                ->exists();
    }
}
