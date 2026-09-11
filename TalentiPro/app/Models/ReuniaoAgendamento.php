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

    public function feedback()
    {
        return $this->hasOne(ReuniaoFeedback::class, 'agendamento_id');
    }

    public function participantes()
    {
        return $this->belongsToMany(User::class, 'reuniao_agendamento_participantes', 'agendamento_id', 'user_id');
    }
}
