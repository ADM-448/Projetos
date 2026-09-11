<?php

namespace App\Enums;

enum StatusEmpresa: string
{
    case ATIVA = 'ativa';
    case SUSPENSA = 'suspensa';
    case CANCELADA = 'cancelada';
}
