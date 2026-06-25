<?php

namespace App\Exceptions;

use RuntimeException;

class UnauthorizedException extends RuntimeException
{
    public function __construct(string $message = 'Unauthenticated.')
    {
        parent::__construct($message, 401);
    }
}
