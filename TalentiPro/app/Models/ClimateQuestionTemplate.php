<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClimateQuestionTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'dimensao',
        'texto_pergunta',
        'tipo_resposta',
        'is_global',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
