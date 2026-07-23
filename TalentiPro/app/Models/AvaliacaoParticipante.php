<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvaliacaoParticipante extends Model
{
    use HasFactory;

    protected $table = 'avaliacao_participantes'; // Conforme seu print

    protected $fillable = [
        'avaliacao_id',
        'avaliado_id',
        'avaliador_id',
        'tipo', // autoavaliacao, gestor, par, subordinado
        'status', // pendente, realizada
        'nota',
        'comentario',
        'observacao_avaliado'
    ];


    public function avaliacao()
    {
        return $this->belongsTo(Avaliacao::class);
    }

    public function avaliado()
    {
        return $this->belongsTo(User::class, 'avaliado_id');
    }

    public function avaliador()
    {
        return $this->belongsTo(User::class, 'avaliador_id');
    }

    public function mediador()
    {
        return $this->belongsTo(User::class, 'mediador_id');
    }

    public function respostas()
    {
        return $this->hasMany(AvaliacaoResposta::class);
    }
}
