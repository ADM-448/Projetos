<?php

namespace App\Models;

use App\Models\Traits\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoQuestaoIa extends Model
{
    use HasFactory, BelongsToEmpresa;

    protected $table = 'cargo_questao_ias';

    protected $fillable = [
        'empresa_id',
        'cargo_id',
        'hash_descricao',
        'questoes_sugeridas',
        'modelo_ia',
    ];

    protected $casts = [
        'questoes_sugeridas' => 'array',
    ];

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }
}
