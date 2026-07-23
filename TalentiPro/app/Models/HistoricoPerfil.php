<?php

namespace App\Models;

use App\Models\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

class HistoricoPerfil extends Model
{
    use BelongsToEmpresa;

    protected $table = 'historico_perfils';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'perfil_anterior',
        'perfil_novo',
        'alterado_por',
        'motivo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function alteradoPor()
    {
        return $this->belongsTo(User::class, 'alterado_por');
    }
}
