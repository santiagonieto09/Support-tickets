<?php

declare(strict_types=1);

namespace App\Domain\Exception;

/**
 * Exception thrown when a user tries to access a resource they don't own.
 */
class AccessDeniedException extends DomainException
{
    public function __construct(string $message = 'Access denied to this resource')
    {
        parent::__construct($message);
    }
}
