<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidato extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nome_completo',
        'data_nascimento',
        'telefone',
        'whatsapp',
        'celular',
        'cep',
        'logradouro',
        'bairro',
        'cidade',
        'estado',
        'latitude',
        'longitude',
        'sobre_mim',
        'resumo_profissional',
        'situacao_profissional',
        'cargo_pretendido',
        'faixa_salarial',
        'escolaridade',
        'sexo',
        'estado_civil',
        'nacionalidade',
        'rne',
        'curriculo_path',
        'idiomas',
        'carteira_habilitacao',
    ];

    protected function casts(): array
    {
        return [
            'data_nascimento' => 'date',
            'idiomas' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function experiencias(): HasMany
    {
        return $this->hasMany(ExperienciaProfissional::class)->orderByDesc('data_inicio');
    }

    public function formacoes(): HasMany
    {
        return $this->hasMany(FormacaoAcademica::class)->orderByDesc('data_inicio');
    }

    public function cursos(): HasMany
    {
        return $this->hasMany(CursoComplementar::class)->orderByDesc('ano_conclusao');
    }

    public function candidaturas(): HasMany
    {
        return $this->hasMany(Candidatura::class);
    }

    /**
     * Valida se os dados mínimos necessários para candidatura foram preenchidos
     * Conforme regra de negócio: dados mínimos até o 'sobre_mim' (exceto resumo profissional).
     */
    public function isPerfilCompleto(): bool
    {
        $camposObrigatorios = [
            'data_nascimento',
            'cep',
            'cidade',
            'estado',
            'sobre_mim',
        ];

        foreach ($camposObrigatorios as $campo) {
            if (empty($this->{$campo})) {
                return false;
            }
        }

        // Pelo menos um telefone ou celular preenchido
        if (empty($this->telefone) && empty($this->celular)) {
            return false;
        }

        return true;
    }
}
