<?php

namespace App\Models;

use App\Models\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

class SolicitacaoAvaliacao extends Model
{
    use BelongsToEmpresa;

    protected $table = 'solicitacao_avaliacaos';

    protected $fillable = [
        'empresa_id',
        'colaborador_id',
        'motivo',
        'status',
        'aprovador_id',
        'observacao_aprovador',
    ];

    public function colaborador()
    {
        return $this->belongsTo(User::class, 'colaborador_id');
    }

    public function aprovador()
    {
        return $this->belongsTo(User::class, 'aprovador_id');
    }
}
