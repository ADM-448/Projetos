<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'role',
        'is_superadmin',
        'is_active',
        'name',
        'email',
        'password',
        'sip_extension',
        'sip_secret',
        'sip_webrtc_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'sip_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_superadmin' => 'boolean',
            'is_active' => 'boolean',
            'sip_webrtc_enabled' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function callLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CallLog::class);
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_superadmin || $this->role === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return $this->isSuperAdmin() || $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->isAdmin() || $this->role === 'manager';
    }
}
