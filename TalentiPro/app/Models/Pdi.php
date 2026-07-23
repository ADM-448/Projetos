<?php

namespace App\Models;

use App\Models\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

class Pdi extends Model
{
    use BelongsToEmpresa;

    protected $fillable = [
        'empresa_id',
        'colaborador_id',
        'gestor_id',
        'titulo',
        'descricao',
        'prazo',
        'data_inicio',
        'data_fim',
        'data_conclusao',
        'status',
        'progresso',
    ];

    protected $casts = [
        'prazo' => 'date',
    ];

    public function colaborador()
    {
        return $this->belongsTo(User::class, 'colaborador_id');
    }

    public function gestor()
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    public function acoes()
    {
        return $this->hasMany(PdiAcao::class);
    }

    public function comentarios()
    {
        return $this->hasMany(PdiComentario::class)->orderBy('created_at', 'desc');
    }
}
