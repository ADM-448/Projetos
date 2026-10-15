<?php

namespace App\Models;

use App\Models\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvaliacaoSchedule extends Model
{
    use HasFactory, BelongsToEmpresa;

    protected $table = 'avaliacao_schedules';

    protected $fillable = [
        'empresa_id',
        'template_avaliacao_id',
        'recorrencia',
        'proxima_execucao',
        'dias_duracao',
        'config_delegacao',
    ];

    protected $casts = [
        'proxima_execucao' => 'date',
        'config_delegacao' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(Avaliacao::class, 'template_avaliacao_id');
    }
}
