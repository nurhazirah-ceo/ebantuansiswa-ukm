<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'matrik',
        'email',
        'password',
        'role',
        'account_status',
        'profile_photo_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relationship: User → Donor
     * 1 user ada 1 profil penderma
     */
    public function donor(): HasOne
    {
        return $this->hasOne(Donor::class);
    }

    public function permohonan(): HasMany
    {
        return $this->hasMany(Permohonan::class);
    }

    public function sumbangans(): HasMany
    {
        return $this->hasMany(Sumbangan::class);
    }

    public function cashDonations(): HasMany
    {
        return $this->hasMany(CashDonation::class);
    }

    public function physicalDonations(): HasMany
    {
        return $this->hasMany(PhysicalDonation::class);
    }

    public function sendPasswordResetNotification($token)
{
    $this->notify(new ResetPasswordNotification($token));
}

}
