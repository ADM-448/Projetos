<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExperienciaProfissional extends Model
{
    use HasFactory;

    protected $table = 'experiencias_profissionais';

    protected $fillable = [
        'candidato_id',
        'empresa',
        'cargo',
        'data_inicio',
        'data_fim',
        'atual',
        'descricao',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_fim' => 'date',
            'atual' => 'boolean',
        ];
    }

    public function candidato(): BelongsTo
    {
        return $this->belongsTo(Candidato::class);
    }
}
