<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReuniaoFeedback extends Model
{
    protected $table = 'reuniao_feedbacks';

    protected $fillable = [
        'gestor_id',
        'agendamento_id',
        'transcricao_original',
        'transcricao_completa',
        'resumo_ia',
        'resumo_ia_original',
        'sentimento_ia',
        'audio_path',
    ];

    public function gestor()
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    public function agendamento()
    {
        return $this->belongsTo(ReuniaoAgendamento::class, 'agendamento_id');
    }

    public function participantes()
    {
        return $this->belongsToMany(User::class, 'reuniao_feedback_participantes', 'reuniao_feedback_id', 'user_id');
    }

    public function pdis()
    {
        return $this->hasMany(Pdi::class, 'reuniao_feedback_id');
    }
}
