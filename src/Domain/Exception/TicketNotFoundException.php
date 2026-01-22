<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Symfony\Component\Uid\Uuid;

/**
 * Exception thrown when a ticket is not found.
 */
class TicketNotFoundException extends DomainException
{
    public function __construct(Uuid|string $id)
    {
        $idString = $id instanceof Uuid ? $id->toRfc4122() : $id;
        parent::__construct(sprintf('Ticket with ID "%s" not found', $idString));
    }
}
