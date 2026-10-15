<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConvencaoRegra extends Model
{
    use HasFactory;

    protected $fillable = [
        'convencao_id',
        'tipo_regra',
        'descricao',
        'valor'
    ];

    public function convencao()
    {
        return $this->belongsTo(Convencao::class);
    }
}
