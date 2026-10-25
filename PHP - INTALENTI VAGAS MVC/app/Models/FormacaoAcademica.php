<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormacaoAcademica extends Model
{
    use HasFactory;

    protected $table = 'formacoes_academicas';

    protected $fillable = [
        'candidato_id',
        'instituicao',
        'curso',
        'nivel',
        'data_inicio',
        'data_fim',
        'concluido',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_fim' => 'date',
            'concluido' => 'boolean',
        ];
    }

    public function candidato(): BelongsTo
    {
        return $this->belongsTo(Candidato::class);
    }
}
