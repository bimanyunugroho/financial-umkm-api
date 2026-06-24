<?php

namespace App\Exceptions;

use RuntimeException;

class TooManyRequestsException extends RuntimeException
{
    public function __construct(string $message = 'Terlalu banyak request.')
    {
        parent::__construct($message, 429);
    }
}