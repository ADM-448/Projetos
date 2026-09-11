<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdiAcao extends Model
{
    // A tabela correspondente é pdi_acoes
    protected $table = 'pdi_acoes';

    protected $fillable = [
        'pdi_id',
        'titulo',
        'descricao',
        'status',
    ];

    /**
     * A ação pertence a um PDI.
     */
    public function pdi(): BelongsTo
    {
        return $this->belongsTo(Pdi::class, 'pdi_id');
    }
}
