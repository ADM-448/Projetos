<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyCadence extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'cadence_id',
        'user_id',
        'current_step_number',
        'status',
        'started_at',
        'next_due_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'next_due_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function cadence(): BelongsTo
    {
        return $this->belongsTo(Cadence::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'company_cadence_id');
    }
}
