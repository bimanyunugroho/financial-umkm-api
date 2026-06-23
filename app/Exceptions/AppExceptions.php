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

class ForbiddenException extends RuntimeException
{
    public function __construct(string $message = 'Anda tidak memiliki akses.')
    {
        parent::__construct($message, 403);
    }
}

class ConflictException extends RuntimeException
{
    public function __construct(string $message = 'Konflik data.')
    {
        parent::__construct($message, 409);
    }
}

class TooManyRequestsException extends RuntimeException
{
    public function __construct(string $message = 'Terlalu banyak request.')
    {
        parent::__construct($message, 429);
    }
}

class ServiceUnavailableException extends RuntimeException
{
    public function __construct(string $message = 'Layanan tidak tersedia.')
    {
        parent::__construct($message, 503);
    }
}

class UnauthorizedException extends RuntimeException
{
    public function __construct(string $message = 'Unauthenticated.')
    {
        parent::__construct($message, 401);
    }
}
