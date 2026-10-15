<?php

namespace App\Enums;

enum StatusAvaliacao: string
{
    case RASCUNHO   = 'rascunho';
    case ANDAMENTO  = 'andamento';
    case AGENDADA   = 'agendada';
    case CONCLUIDA  = 'concluida';
    case INCOMPLETO = 'incompleto'; // Prazo encerrado e ainda há pendentes

    public function label(): string
    {
        return match($this) {
            self::RASCUNHO   => 'Rascunho',
            self::ANDAMENTO  => 'Em Andamento',
            self::AGENDADA   => 'Agendada',
            self::CONCLUIDA  => 'Concluída',
            self::INCOMPLETO => 'Incompleta',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::RASCUNHO   => '#475569',
            self::ANDAMENTO  => '#166534',
            self::AGENDADA   => '#1e40af',
            self::CONCLUIDA  => '#075985',
            self::INCOMPLETO => '#9a3412',
        };
    }

    public function bg(): string
    {
        return match($this) {
            self::RASCUNHO   => '#f1f5f9',
            self::ANDAMENTO  => '#dcfce7',
            self::AGENDADA   => '#dbeafe',
            self::CONCLUIDA  => '#e0f2fe',
            self::INCOMPLETO => '#fee2e2',
        };
    }

    public function border(): string
    {
        return match($this) {
            self::RASCUNHO   => '#cbd5e1',
            self::ANDAMENTO  => '#bbf7d0',
            self::AGENDADA   => '#bfdbfe',
            self::CONCLUIDA  => '#bae6fd',
            self::INCOMPLETO => '#fecaca',
        };
    }
}
