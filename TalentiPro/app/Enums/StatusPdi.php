<?php

namespace App\Enums;

enum StatusPdi: string
{
    case NAO_INICIADO = 'nao_iniciado';
    case EM_ANDAMENTO = 'em_andamento';
    case CONCLUIDO = 'concluido';
    case ATRASADO = 'atrasado';
    case CANCELADO = 'cancelado';
}
