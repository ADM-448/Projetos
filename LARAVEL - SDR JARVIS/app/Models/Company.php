<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Company extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'pipeline_stage_id',
        'assigned_user_id',
        'name',
        'document',
        'segment',
        'status',
        'custom_attributes',
        'meeting_at',
        'meeting_link',
    ];

    protected $casts = [
        'custom_attributes' => 'array',
        'meeting_at' => 'datetime',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Lead::class, 'company_id');
    }

    public function leads(): HasMany
    {
        return $this->contacts();
    }

    public function messages(): HasManyThrough
    {
        return $this->hasManyThrough(Message::class, Lead::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->latest();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END, due_at ASC");
    }

    public function cadenceEnrollments(): HasMany
    {
        return $this->hasMany(CompanyCadence::class)->latest();
    }

    public function activeCadence(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CompanyCadence::class)->where('status', 'active')->latestOfMany();
    }

    public function latestCadence(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CompanyCadence::class)->latestOfMany();
    }

    public function callLogs(): HasMany
    {
        return $this->hasMany(CallLog::class)->latest();
    }
}
