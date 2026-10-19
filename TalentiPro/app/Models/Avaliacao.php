<?php

namespace App\Models;

use App\Enums\StatusAvaliacao;
use App\Models\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Avaliacao extends Model
{
    use HasFactory, BelongsToEmpresa;

    protected $table = 'avaliacoes';

    protected $fillable = [
        'empresa_id',
        'grau',
        'peso_tecnico',
        'peso_comportamental',
        'titulo',
        'descricao',
        'filtros_aplicados',
        'mediadores',
        'data_inicio',
        'data_fim',
        'status',
        'is_template',
        'template_id',
        'recorrencia',
        'created_by',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'status' => StatusAvaliacao::class,
        'filtros_aplicados' => 'array',
        'mediadores' => 'array',
        'is_template' => 'boolean',
    ];

    public function criador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Avaliacao::class, 'template_id');
    }

    public function campanhas(): HasMany
    {
        return $this->hasMany(Avaliacao::class, 'template_id');
    }

    public function competencias(): BelongsToMany
    {
        return $this->belongsToMany(Competencia::class, 'avaliacao_competencias', 'avaliacao_id', 'competencia_id')
                    ->withPivot('peso')
                    ->withTimestamps();
    }

    public function participantes(): HasMany
    {
        return $this->hasMany(AvaliacaoParticipante::class, 'avaliacao_id');
    }

    public function fechamentos(): HasMany
    {
        return $this->hasMany(AvaliacaoFechamento::class, 'avaliacao_id');
    }

    /**
     * Recalcula e persiste o status da avaliação de acordo com as regras de negócio:
     *  - CONCLUIDA  → todos os participantes (exceto mediadores) responderam
     *  - INCOMPLETO → existe prazo encerrado e ainda há pendentes
     *  - ANDAMENTO  → caso contrário
     */
    public function atualizarStatus(): void
    {
        // Não toca em rascunhos ou avaliações agendadas
        if (in_array($this->status, [StatusAvaliacao::RASCUNHO, StatusAvaliacao::AGENDADA])) {
            return;
        }

        $participantes = $this->participantes()
            ->whereNotIn('tipo', ['mediador'])
            ->get();

        if ($participantes->isEmpty()) {
            return;
        }

        $totalPendentes = $participantes->where('status', 'pendente')->count();

        if ($totalPendentes === 0) {
            $novoStatus = StatusAvaliacao::CONCLUIDA;
        } elseif ($this->data_fim && \Carbon\Carbon::parse($this->data_fim)->endOfDay()->isPast()) {
            // Prazo encerrado e ainda há pendentes
            $novoStatus = StatusAvaliacao::INCOMPLETO;
        } else {
            $novoStatus = StatusAvaliacao::ANDAMENTO;
        }

        if ($this->status !== $novoStatus) {
            $this->update(['status' => $novoStatus]);
        }
    }
}
