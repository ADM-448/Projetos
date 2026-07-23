<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Lançada quando um usuário tenta responder uma pesquisa
 * de clima que já respondeu anteriormente.
 */
final class DuplicateParticipationException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Você já participou desta pesquisa de clima organizacional.');
    }
}
