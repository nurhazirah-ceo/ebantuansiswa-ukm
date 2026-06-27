<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donor extends Model
{
    protected $fillable = [
        'user_id',
        'donor_type',
        'representative_name',
        'phone',
        'alt_phone',
        'preferred_contact',
        'admin_note',
        'support_document',

        // Homepage
        'logo',
        'homepage_label',
        'homepage_order',
        'show_on_homepage',
    ];

    protected $casts = [
        'show_on_homepage' => 'boolean',
        'homepage_order' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->hasOne(Address::class);
    }
}
