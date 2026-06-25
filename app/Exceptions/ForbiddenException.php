<?php

namespace App\Exceptions;

use RuntimeException;

class ForbiddenException extends RuntimeException
{
    public function __construct(string $message = 'Anda tidak memiliki akses.')
    {
        parent::__construct($message, 403);
    }
}