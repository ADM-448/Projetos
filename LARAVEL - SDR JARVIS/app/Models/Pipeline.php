<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pipeline extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class)->orderBy('position');
    }
}
