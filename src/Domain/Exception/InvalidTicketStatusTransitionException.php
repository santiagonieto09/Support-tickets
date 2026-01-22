<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use App\Domain\ValueObject\TicketStatus;

/**
 * Exception thrown when an invalid ticket status transition is attempted.
 */
class InvalidTicketStatusTransitionException extends DomainException
{
    public function __construct(
        private readonly TicketStatus $currentStatus,
        private readonly TicketStatus $attemptedStatus
    ) {
        parent::__construct(
            sprintf(
                'Cannot transition ticket from "%s" to "%s"',
                $currentStatus->value,
                $attemptedStatus->value
            )
        );
    }

    public function getCurrentStatus(): TicketStatus
    {
        return $this->currentStatus;
    }

    public function getAttemptedStatus(): TicketStatus
    {
        return $this->attemptedStatus;
    }
}
