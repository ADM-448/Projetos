<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'cpf',
        'tipo_usuario', // 1=Candidato, 2=Empresa/RH, 3=Admin
        'portal_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tipo_usuario' => 'integer',
            'status' => 'boolean',
        ];
    }

    public function isCandidato(): bool
    {
        return $this->tipo_usuario === 1;
    }

    public function isEmpresa(): bool
    {
        return $this->tipo_usuario === 2;
    }

    public function isAdmin(): bool
    {
        return $this->tipo_usuario === 3;
    }

    public function isSuperAdmin(): bool
    {
        return $this->id === 1 || $this->email === 'admin@intalenti.com.br' || ($this->tipo_usuario === 3 && !$this->empresas()->where('tipo', 'prefeitura')->exists());
    }

    public function isEmpresaAdmin(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->empresas()->wherePivot('role', 'admin')->exists();
    }

    public function getEmpresa(): ?Empresa
    {
        return $this->empresas()->first();
    }

    public function isPrefeitura(): bool
    {
        return $this->tipo_usuario === 3 || $this->empresas()->where('tipo', 'prefeitura')->exists();
    }

    public function getCidadePrefeitura(): string
    {
        $pref = $this->empresas()->where('tipo', 'prefeitura')->first();
        if ($pref && !empty($pref->cidade)) {
            return $pref->cidade;
        }

        return 'Maringá';
    }

    /**
     * Retorna o primeiro nome (ex: "João" de "João da Silva")
     */
    protected function primeiroNome(): Attribute
    {
        return Attribute::make(
            get: function () {
                $partes = explode(' ', trim($this->name));
                return $partes[0] ?? $this->name;
            }
        );
    }

    public function candidato(): HasOne
    {
        return $this->hasOne(Candidato::class);
    }

    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'empresa_usuarios')
            ->withPivot('role', 'status')
            ->withTimestamps();
    }

    public function vagasCriadas(): HasMany
    {
        return $this->hasMany(Vaga::class, 'recrutador_id');
    }

    public function notificacoes(): HasMany
    {
        return $this->hasMany(Notificacao::class)->orderByDesc('created_at');
    }
}
