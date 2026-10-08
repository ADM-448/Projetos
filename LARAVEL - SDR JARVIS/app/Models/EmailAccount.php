<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailAccount extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'email',
        'name',
        'from_name',
        'avatar_url',
        'provider',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'status',
        'daily_limit',
        'sent_today',
        'last_active_at',
        'last_error',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'last_active_at' => 'datetime',
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
