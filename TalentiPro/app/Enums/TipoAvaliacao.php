<?php

namespace App\Enums;

enum TipoAvaliacao: string
{
    case AUTOAVALIACAO = 'autoavaliacao';
    case GESTOR = 'gestor';
    case PARES = 'pares';
    case SUBORDINADO = 'subordinado';
    case MEDIADOR = 'mediador';
}
