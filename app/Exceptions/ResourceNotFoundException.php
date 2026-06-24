<?php

namespace App\Exceptions;

use RuntimeException;

class ResourceNotFoundException extends RuntimeException
{
    public function __construct(string $message = 'Data tidak ditemukan.')
    {
        parent::__construct($message, 404);
    }
}