<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallLog extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'lead_id',
        'user_id',
        'uniqueid',
        'phone_dialed',
        'direction',
        'status',
        'duration',
        'billable_seconds',
        'outcome',
        'notes',
        'recording_url',
        'transcription',
        'ai_summary',
    ];

    protected $casts = [
        'duration' => 'integer',
        'billable_seconds' => 'integer',
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
}
