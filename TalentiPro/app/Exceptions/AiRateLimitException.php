<?php

namespace App\Exceptions;

use Exception;

class AiRateLimitException extends Exception
{
    // Lançada quando a API estoura o limite de cota / rate limit (ex: HTTP 429 ou Quota Exceeded)
}
