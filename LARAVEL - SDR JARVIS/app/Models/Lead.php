<?php

namespace App\Models;

use App\Models\PipelineStage;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User;

class Lead extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'pipeline_stage_id',
        'assigned_user_id',
        'name',
        'job_title',
        'company_name',
        'phone',
        'email',
        'status',
        'is_primary',
        'custom_attributes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'custom_attributes' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Verifica se o SDR autônomo está pausado para este lead específico.
     */
    public function isAiAutonomousPaused(): bool
    {
        $attrs = $this->custom_attributes ?? [];
        return (bool) ($attrs['ai_autonomous_paused'] ?? false);
    }

    /**
     * Pausa a IA autônoma para que um vendedor humano assuma a conversa.
     */
    public function pauseAiAutonomous(string $reason = ''): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['ai_autonomous_paused'] = true;
        $attrs['ai_autonomous_paused_at'] = now()->toIso8601String();
        $attrs['ai_autonomous_pause_reason'] = $reason;

        $this->custom_attributes = $attrs;
        $this->save();
    }

    /**
     * Reativa o atendimento autônomo da IA para este lead.
     */
    public function resumeAiAutonomous(): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['ai_autonomous_paused'] = false;
        $attrs['ai_autonomous_resumed_at'] = now()->toIso8601String();

        $this->custom_attributes = $attrs;
        $this->save();
    }
}
