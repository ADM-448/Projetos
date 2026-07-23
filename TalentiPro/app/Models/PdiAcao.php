<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdiAcao extends Model
{
    protected $table = 'pdi_acaos';

    protected $fillable = [
        'pdi_id',
        'titulo',
        'descricao',
        'status',
    ];

    public function pdi()
    {
        return $this->belongsTo(Pdi::class);
    }
}
