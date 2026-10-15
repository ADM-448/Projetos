<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\StatusUsuario;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'cpf',
        'telefone',
        'perfil_acesso',
        'status',
        'empresa_id',
        'departamento_id',
        'cargo_id',
        'gestor_id',
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
            'perfil_acesso' => UserRole::class, // Conversão automática para Enum!
            'status' => StatusUsuario::class,   // Conversão automática para Enum!
        ];
    }

    // E adicione os relacionamentos abaixo da função empresa():
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class);
    }

    public function gestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    public function subordinados(): HasMany
    {
        return $this->hasMany(User::class, 'gestor_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function atividades(): HasMany
    {
        return $this->hasMany(Atividade::class);
    }

    public function discAssessment(): HasOne
    {
        return $this->hasOne(DiscAssessment::class)->latestOfMany();
    }

    public function discAssessments(): HasMany
    {
        return $this->hasMany(DiscAssessment::class)->latest();
    }

    /**
     * Empresas às quais o usuário tem acesso como Consultor.
     */
    public function empresasConsultoria(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'consultor_empresas', 'user_id', 'empresa_id')
            ->withTimestamps();
    }

    /**
     * Verifica se o usuário possui o perfil Consultor.
     */
    public function isConsultor(): bool
    {
        $role = $this->perfil_acesso instanceof \BackedEnum
            ? $this->perfil_acesso->value
            : $this->perfil_acesso;

        return $role === UserRole::CONSULTOR->value;
    }
}
