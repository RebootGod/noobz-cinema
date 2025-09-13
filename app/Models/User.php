<?php
// ========================================
// 1. USER MODEL
// ========================================
// File: app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'status',
        'last_login_at',
        'last_login_ip'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function inviteCodes()
    {
        return $this->hasMany(InviteCode::class, 'created_by');
    }

    public function registration()
    {
        return $this->hasOne(UserRegistration::class);
    }

    public function movieViews()
    {
        return $this->hasMany(MovieView::class);
    }

    public function addedMovies()
    {
        return $this->hasMany(Movie::class, 'added_by');
    }

    // Helper Methods
    public function isAdmin()
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function updateLastLogin()
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip()
        ]);
    }
}