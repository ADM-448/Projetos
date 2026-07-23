<?php

namespace App\Models;

use App\Models\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avaliacao extends Model
{
    use HasFactory, BelongsToEmpresa;

    // CORREÇÃO 1: Nome da tabela conforme seu print
    protected $table = 'avaliacaos';

    protected $fillable = [
        'titulo',
        'descricao',
        'data_inicio',
        'data_fim',
        'status',
        'forma_avaliacao',   // CORREÇÃO 2: Mudou de tipo_avaliacao para forma_avaliacao
        'senioridade',       // Novo campo
        'data_admissao',     // Novo campo
        'created_by',        // Quem criou a avaliação
        'empresa_id',
        'filtros'            // Novo campo JSON
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'data_admissao' => 'date',
        'filtros' => 'array', // Cast automático para array
    ];

    public function participantes()
    {
        return $this->hasMany(AvaliacaoParticipante::class, 'avaliacao_id');
    }

    // Relacionamento Muitos-para-Muitos
    public function competencias()
    {
        // Cria uma tabela pivo 'avaliacao_competencia' automaticamente se usar a convenção
        // Mas como estamos fazendo na raça, vamos assumir que você vai criar a tabela pivô
        return $this->belongsToMany(Competencia::class, 'avaliacao_competencias', 'avaliacao_id', 'competencia_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
