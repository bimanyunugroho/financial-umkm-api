<?php

namespace App\Exceptions;

use RuntimeException;

class ConflictException extends RuntimeException
{
    public function __construct(string $message = 'Konflik data.')
    {
        parent::__construct($message, 409);
    }
}