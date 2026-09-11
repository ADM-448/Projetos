<?php

namespace App\Models;

use App\Models\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Solicitacao extends Model
{
    use BelongsToEmpresa;

    protected $table = 'solicitacoes';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'avaliacao_id',
        'disc_assessment_id',
        'tipo',
        'justificativa',
        'status',
        'resposta_rh',
    ];

    protected $casts = [
        'tipo'   => 'string',
        'status' => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function avaliacao(): BelongsTo
    {
        return $this->belongsTo(Avaliacao::class);
    }

    public function discAssessment(): BelongsTo
    {
        return $this->belongsTo(DiscAssessment::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function isPendente(): bool
    {
        return $this->status === 'pendente';
    }

    /**
     * Conta as solicitações pendentes de forma segura (não quebra em ambiente sem a tabela).
     */
    public static function contarPendentes(?int $userId = null, ?int $gestorId = null): int
    {
        try {
            $q = static::where('status', 'pendente');
            if ($userId) {
                $q->where('user_id', $userId);
            }
            if ($gestorId) {
                $q->whereHas('user', fn($u) => $u->where('gestor_id', $gestorId));
            }
            return $q->count();
        } catch (\Throwable) {
            return 0;
        }
    }
}
