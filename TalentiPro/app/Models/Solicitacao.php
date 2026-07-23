<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitacao extends Model
{
    use HasFactory;
    
    protected $table = 'solicitacoes';

    protected $fillable = [
        'user_id',
        'avaliacao_id',
        'tipo',
        'justificativa',
        'status',
        'resposta_rh'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function avaliacao()
    {
        return $this->belongsTo(Avaliacao::class);
    }
}
