<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReuniaoFeedback extends Model
{
    protected $table = 'reuniao_feedback';
    
    protected $fillable = [
        'titulo',
        'gestor_id',
        'agendamento_id',
        'transcricao',
        'resumo',
        'audio_path',
        'status',
    ];

    public function gestor()
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    public function participantes()
    {
        return $this->belongsToMany(User::class, 'reuniao_feedback_participantes', 'reuniao_feedback_id', 'user_id');
    }
}
