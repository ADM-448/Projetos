<?php

namespace App\Models;

use App\Enums\StatusEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'cnpj',
        'razao_social',
        'email',
        'telefone',
        'logo',
        'status',
        'max_usuarios',
        'data_contratacao',
        'ia_ativa',
        'ia_limite_transcricoes_mes',
        'ia_transcricoes_utilizadas',
        'ia_limite_reset_em',
        'whatsapp_ativo',
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function configuracaoGoogle()
    {
        return $this->hasOne(ConfiguracaoGoogle::class);
    }

    protected $casts = [
        'status' => StatusEmpresa::class,
        'data_contratacao' => 'date',
        'ia_ativa' => 'boolean',
        'ia_limite_reset_em' => 'date',
        'whatsapp_ativo' => 'boolean',
    ];
}
