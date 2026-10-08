<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CadenceStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'cadence_id',
        'step_number',
        'day_offset',
        'delay_unit',
        'delay_value',
        'task_type',
        'title',
        'instructions',
        'template_content',
        'priority',
    ];

    public function cadence(): BelongsTo
    {
        return $this->belongsTo(Cadence::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
