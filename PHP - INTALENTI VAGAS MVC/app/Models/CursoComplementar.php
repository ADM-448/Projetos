<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CursoComplementar extends Model
{
    use HasFactory;

    protected $table = 'cursos_complementares';

    protected $fillable = [
        'candidato_id',
        'nome_curso',
        'instituicao',
        'carga_horaria',
        'ano_conclusao',
    ];

    public function candidato(): BelongsTo
    {
        return $this->belongsTo(Candidato::class);
    }
}
