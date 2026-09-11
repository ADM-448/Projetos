<?php

namespace App\Enums;

enum StatusUsuario: string
{
    case ATIVO = 'ativo';
    case INATIVO = 'inativo';
    case FERIAS = 'ferias';
}
