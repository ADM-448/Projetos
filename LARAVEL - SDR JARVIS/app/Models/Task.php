<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'lead_id',
        'company_cadence_id',
        'cadence_id',
        'cadence_step_id',
        'user_id',
        'title',
        'description',
        'type',
        'priority',
        'due_at',
        'completed_at',
        'status',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cadence(): BelongsTo
    {
        return $this->belongsTo(Cadence::class);
    }

    public function cadenceStep(): BelongsTo
    {
        return $this->belongsTo(CadenceStep::class);
    }

    public function companyCadence(): BelongsTo
    {
        return $this->belongsTo(CompanyCadence::class);
    }

    /**
     * Scope para tarefas pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para tarefas concluídas
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
