<?php

namespace App\Models;

use App\Models\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PdiGrupo extends Model
{
    use BelongsToEmpresa;

    protected $fillable = [
        'empresa_id',
        'gestor_id',
        'titulo',
        'descricao',
        'categoria',
        'data_inicio',
        'data_fim',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    /**
     * O grupo pertence a um gestor que o criou.
     */
    public function gestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    /**
     * Um grupo de PDI possui vários PDIs individuais (um por colaborador).
     */
    public function pdis(): HasMany
    {
        return $this->hasMany(Pdi::class, 'pdi_grupo_id');
    }
}
