<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cadence extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'target_segment',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(CadenceStep::class)->orderBy('step_number', 'asc');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CompanyCadence::class);
    }

    public function activeEnrollments(): HasMany
    {
        return $this->hasMany(CompanyCadence::class)->where('status', 'active');
    }
}
