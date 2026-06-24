<?php

namespace App\Exceptions;

use RuntimeException;

class ServiceUnavailableException extends RuntimeException
{
    public function __construct(string $message = 'Layanan tidak tersedia.')
    {
        parent::__construct($message, 503);
    }
}