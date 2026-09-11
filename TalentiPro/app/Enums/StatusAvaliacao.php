<?php

namespace App\Enums;

enum StatusAvaliacao: string
{
    case RASCUNHO = 'rascunho';
    case ANDAMENTO = 'andamento';
    case AGENDADA = 'agendada';
    case CONCLUIDA = 'concluida';
}
