<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvaliacaoFechamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'avaliacao_id',
        'avaliado_id',
        'responsavel_id',
        'nota_final',
        'comentarios',
        'plano_acao'
    ];

    public function avaliacao()
    {
        return $this->belongsTo(Avaliacao::class);
    }

    public function avaliado()
    {
        return $this->belongsTo(User::class, 'avaliado_id');
    }

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }
}
