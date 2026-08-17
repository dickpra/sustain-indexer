<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// 🔥 MAGIC: Tambahkan implement MustVerifyEmail dan FilamentUser
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // =======================================================
    // 🔥 FITUR ISOLASI AKSES: ADMIN VS PUBLISHER
    // =======================================================
    public function canAccessPanel(Panel $panel): bool
    {
        // 1. Panel Administrator HANYA untuk email dewa ini
        if ($panel->getId() === 'administrator') {
            return $this->email === 'admin@sustaindex.com';
        }

        // 2. Panel Publisher untuk selain admin
        if ($panel->getId() === 'publisher') {
            return $this->email !== 'admin@sustaindex.com';
        }

        return true;
    }
}