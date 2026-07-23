<?php

namespace App\Models;

use App\Models\Traits\BelongsToEmpresa;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, BelongsToEmpresa;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'empresa_id',
        'name',
        'email',
        'password',
        'cpf',
        'telefone',
        'data_nascimento',
        'genero',
        'escolaridade',
        'foto_perfil',
        'data_admissao',
        'data_rescisao', // Adicionado
        'status',
        'vinculo',
        'departamento_id',
        'cargo_id',
        'gestor_id',
        'perfil_acesso',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'data_nascimento' => 'date',
            'data_admissao' => 'date',
            'data_rescisao' => 'date', // Adicionado
        ];
    }

    // --- RELACIONAMENTOS ---

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }

    public function gestor()
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    public function subordinados()
    {
        return $this->hasMany(User::class, 'gestor_id');
    }

    public function avaliacoesRecebidas()
    {
        return $this->hasMany(AvaliacaoParticipante::class, 'avaliado_id');
    }

    public function avaliacoesRealizadas()
    {
        return $this->hasMany(AvaliacaoParticipante::class, 'avaliador_id');
    }

    public function pdis()
    {
        return $this->hasMany(Pdi::class, 'colaborador_id');
    }

    public function notificacoes()
    {
        return $this->hasMany(Notificacao::class, 'destinatario_id');
    }

    public function solicitacoesAvaliacao()
    {
        return $this->hasMany(SolicitacaoAvaliacao::class, 'colaborador_id');
    }
}
