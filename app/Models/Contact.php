<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'name',
        'mobile',
        'phone',
        'description',
        'contact_type',
        'avatar_path',
        'archived_at',
        'created_by',
    ];
}
