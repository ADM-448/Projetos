<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case MASTER = 'master';
    case GESTOR = 'gestor';
    case COLABORADOR = 'colaborador';
    case CONSULTOR = 'consultor';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador Global',
            self::MASTER => 'Master',
            self::GESTOR => 'Gestor',
            self::COLABORADOR => 'Colaborador',
            self::CONSULTOR => 'Consultor',
        };
    }
}
