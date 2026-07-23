<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReuniaoAgendamento extends Model
{
    protected $table = 'reuniao_agendamentos';

    protected $fillable = [
        'gestor_id',
        'colaborador_id',
        'titulo',
        'data_hora',
        'link_jitsi',
        'google_event_id',
        'google_meet_link',
        'google_transcript_file_id',
        'status',
        'transcricao_temporaria'
    ];

    protected $casts = [
        'data_hora' => 'datetime',
    ];

    public function gestor()
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    public function colaborador()
    {
        return $this->belongsTo(User::class, 'colaborador_id');
    }

    public function reuniaoFeedback()
    {
        return $this->hasOne(ReuniaoFeedback::class, 'agendamento_id');
    }
}
