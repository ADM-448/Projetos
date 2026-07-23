<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClimateCategory extends Model
{
    use HasFactory, BelongsToEmpresa;

    protected $table = 'climate_categories';

    protected $fillable = [
        'empresa_id',
        'nome',
        'descricao',
        'cor',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(ClimateQuestion::class, 'category_id');
    }
}
