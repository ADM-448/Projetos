<?php

namespace App\Models;

use App\Models\Scopes\PortalScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

#[ScopedBy([PortalScope::class])]
class Vaga extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'recrutador_id',
        'portal_id',
        'titulo',
        'tipo_contrato',
        'modalidade',
        'localizacao',
        'cep',
        'ramo_atividade',
        'descricao',
        'requisitos',
        'beneficios',
        'confidencial',
        'tipo_vaga',
        'experiencia',
        'faixa_salarial',
        'faixa_salarial_min',
        'faixa_salarial_max',
        'salario_a_combinar',
        'latitude',
        'longitude',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'confidencial' => 'boolean',
            'salario_a_combinar' => 'boolean',
            'faixa_salarial_min' => 'decimal:2',
            'faixa_salarial_max' => 'decimal:2',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function recrutador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recrutador_id');
    }

    public function perguntas(): HasMany
    {
        return $this->hasMany(PerguntaVaga::class);
    }

    public function candidaturas(): HasMany
    {
        return $this->hasMany(Candidatura::class);
    }

    public function scopeAtivas(Builder $query): Builder
    {
        return $query->where('status', 'ativa');
    }

    public function scopeBuscar(Builder $query, ?string $termo): Builder
    {
        if (empty($termo)) {
            return $query;
        }

        return $query->where(function ($q) use ($termo) {
            $q->whereFullText(['titulo', 'descricao'], $termo)
              ->orWhere('titulo', 'LIKE', "%{$termo}%")
              ->orWhere('descricao', 'LIKE', "%{$termo}%")
              ->orWhereHas('empresa', function ($empQ) use ($termo) {
                  $empQ->where('confidencial', false)
                       ->where('nome_empresa', 'LIKE', "%{$termo}%");
              });
        });
    }

    public function scopePorRamo(Builder $query, ?string $ramo): Builder
    {
        if (!empty($ramo)) {
            return $query->where('ramo_atividade', 'LIKE', "%{$ramo}%");
        }
        return $query;
    }

    public function scopePorLocalizacao(Builder $query, ?string $local): Builder
    {
        if (!empty($local)) {
            return $query->where('localizacao', 'LIKE', "%{$local}%");
        }
        return $query;
    }

    public function scopePorModalidade(Builder $query, ?string $mod): Builder
    {
        if (!empty($mod)) {
            return $query->where('modalidade', 'LIKE', "%{$mod}%");
        }
        return $query;
    }

    public function scopePorTipoContrato(Builder $query, ?string $tipo): Builder
    {
        if (!empty($tipo)) {
            return $query->where('tipo_contrato', 'LIKE', "%{$tipo}%");
        }
        return $query;
    }

    public function scopePorExperiencia(Builder $query, ?string $exp): Builder
    {
        if (!empty($exp)) {
            return $query->where('experiencia', 'LIKE', "%{$exp}%");
        }
        return $query;
    }

    /**
     * Formatação amigável do salário (ex: "R$ 2.500,00 - R$ 3.500,00" ou "A Combinar")
     */
    protected function salarioFormatado(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->salario_a_combinar) {
                    return 'A combinar';
                }

                if ($this->faixa_salarial_min && $this->faixa_salarial_max) {
                    $min = number_format($this->faixa_salarial_min, 2, ',', '.');
                    $max = number_format($this->faixa_salarial_max, 2, ',', '.');
                    return "R$ {$min} a R$ {$max}";
                }

                if ($this->faixa_salarial_min) {
                    $min = number_format($this->faixa_salarial_min, 2, ',', '.');
                    return "A partir de R$ {$min}";
                }

                if ($this->faixa_salarial_max) {
                    $max = number_format($this->faixa_salarial_max, 2, ',', '.');
                    return "Até R$ {$max}";
                }

                return $this->faixa_salarial ?: 'A combinar';
            }
        );
    }

    /**
     * Tags e competências da vaga (estilo Peerlist)
     */
    protected function tags(): Attribute
    {
        return Attribute::make(
            get: function () {
                $tags = [];
                $techKeywords = [
                    'PHP', 'Laravel', 'MySQL', 'React', 'Node.js', 'Vue', 'JavaScript', 
                    'TypeScript', 'Python', 'Java', 'Docker', 'AWS', 'Figma', 'UI/UX', 
                    'Git', 'Tailwind', 'RESTful APIs', 'PostgreSQL', 'C#', '.NET',
                    'Mobile', 'Flutter', 'Angular', 'DevOps', 'Scrum'
                ];
                $text = ($this->titulo ?? '') . ' ' . ($this->descricao ?? '') . ' ' . ($this->requisitos ?? '');
                foreach ($techKeywords as $kw) {
                    if (stripos($text, $kw) !== false && !in_array($kw, $tags)) {
                        $tags[] = $kw;
                    }
                }

                if (empty($tags) && !empty($this->ramo_atividade)) {
                    $tags[] = $this->ramo_atividade;
                }

                return array_slice($tags, 0, 5);
            }
        );
    }
}

