<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappInstance extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'instance_name',
        'phone_number',
        'status',
        'daily_limit',
        'sent_today',
        'last_active_at',
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
