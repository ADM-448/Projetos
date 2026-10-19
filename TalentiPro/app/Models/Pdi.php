<?php

namespace App\Models;

use App\Enums\StatusPdi;
use App\Models\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pdi extends Model
{
    use BelongsToEmpresa;

    protected $fillable = [
        'empresa_id',
        'colaborador_id',
        'gestor_id',
        'pdi_grupo_id',
        'reuniao_feedback_id',
        'titulo',
        'descricao',
        'data_inicio',
        'data_fim',
        'data_conclusao',
        'status',
        'progresso',
        'comprovante',
        'gerado_ia',
    ];

    protected $casts = [
        'status' => StatusPdi::class,
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'data_conclusao' => 'date',
        'progresso' => 'integer',
        'gerado_ia' => 'boolean',
    ];

    /**
     * Remove os PDIs gerados por IA anteriores do colaborador, preservando
     * os concluídos e todos os criados manualmente (gerado_ia = false).
     */
    public static function removerPdisIaAnteriores(int $colaboradorId): int
    {
        $total = 0;

        static::where('colaborador_id', $colaboradorId)
            ->where('gerado_ia', true)
            ->where('status', '!=', StatusPdi::CONCLUIDO)
            ->get()
            ->each(function (self $pdi) use (&$total) {
                $pdi->delete();
                $total++;
            });

        return $total;
    }

    /**
     * O PDI pertence a um colaborador.
     */
    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'colaborador_id');
    }

    /**
     * O PDI é gerenciado/criado por um gestor (ou master).
     */
    public function gestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    /**
     * O PDI pode pertencer a um grupo de PDIs.
     */
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(PdiGrupo::class, 'pdi_grupo_id');
    }

    /**
     * O PDI pode ter sido gerado a partir de uma reunião de feedback.
     */
    public function reuniaoFeedback(): BelongsTo
    {
        return $this->belongsTo(ReuniaoFeedback::class, 'reuniao_feedback_id');
    }

    /**
     * Um PDI possui várias ações/etapas de desenvolvimento.
     */
    public function acoes(): HasMany
    {
        return $this->hasMany(PdiAcao::class, 'pdi_id');
    }

    /**
     * Um PDI possui vários comentários no chat de alinhamento.
     */
    public function comentarios(): HasMany
    {
        return $this->hasMany(PdiComentario::class, 'pdi_id');
    }
}
