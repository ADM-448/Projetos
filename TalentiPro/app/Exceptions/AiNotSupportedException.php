<?php

namespace App\Exceptions;

use Exception;

class AiNotSupportedException extends Exception
{
    // Lançada quando a API não suporta a operação solicitada (ex: transcrição de áudio no DeepSeek text-only)
}
