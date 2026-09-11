<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case MASTER = 'master';
    case GESTOR = 'gestor';
    case COLABORADOR = 'colaborador';
}
