<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReuniaoAgendamento extends Model
{
    protected $fillable = [
        'gestor_id',
        'colaborador_id',
        'titulo',
        'data_hora',
        'google_event_id',
        'google_meet_link',
        'transcricao_temporaria',
        'status',
        'gravacao_status',
        'gravacao_iniciada_em',
        'gravacao_heartbeat_em',
        'audio_path',
        'transcricao_rascunho',
        'analise_rascunho',
    ];

    protected $casts = [
        'data_hora'             => 'datetime',
        'gravacao_iniciada_em'  => 'datetime',
        'gravacao_heartbeat_em' => 'datetime',
        'analise_rascunho'      => 'array',
    ];

    /** Segundos sem heartbeat para considerar a gravação interrompida. */
    public const HEARTBEAT_TIMEOUT = 15;

    /**
     * Limpa todo o estado de rascunho da gravação.
     */
    public function limparRascunho(): void
    {
        $this->update([
            'gravacao_status'       => 'nenhuma',
            'gravacao_iniciada_em'  => null,
            'gravacao_heartbeat_em' => null,
            'audio_path'            => null,
            'transcricao_rascunho'  => null,
            'analise_rascunho'      => null,
        ]);
    }

    /**
     * Indica se a gravação está marcada como ativa mas parou de enviar heartbeat.
     */
    public function gravacaoInterrompida(): bool
    {
        return $this->gravacao_status === 'gravando'
            && $this->gravacao_heartbeat_em
            && $this->gravacao_heartbeat_em->diffInSeconds(now()) > self::HEARTBEAT_TIMEOUT;
    }

    /**
     * Estado de rascunho exposto à tela da sala (retomada após sair/recarregar).
     */
    public function rascunhoPayload(): array
    {
        return [
            'status'       => $this->gravacao_status,
            'interrompida' => $this->gravacaoInterrompida(),
            'segundos'     => $this->gravacao_iniciada_em ? (int) $this->gravacao_iniciada_em->diffInSeconds(now()) : 0,
            'transcricao'  => $this->transcricao_rascunho,
            'analise'      => $this->analise_rascunho,
        ];
    }

    public function gestor()
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    public function colaborador()
    {
        return $this->belongsTo(User::class, 'colaborador_id');
    }

    public function feedback()
    {
        return $this->hasOne(ReuniaoFeedback::class, 'agendamento_id');
    }

    public function participantes()
    {
        return $this->belongsToMany(User::class, 'reuniao_agendamento_participantes', 'agendamento_id', 'user_id');
    }
}
