<?php

namespace App\Models;

use App\Models\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

class Notificacao extends Model
{
    use BelongsToEmpresa;

    protected $table = 'notificacaos';

    protected $fillable = [
        'empresa_id',
        'destinatario_id',
        'remetente_id',
        'tipo',
        'mensagem',
        'link',
        'lida',
    ];

    protected $casts = [
        'lida' => 'boolean',
    ];

    public function destinatario()
    {
        return $this->belongsTo(User::class, 'destinatario_id');
    }

    public function remetente()
    {
        return $this->belongsTo(User::class, 'remetente_id');
    }
}
