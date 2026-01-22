<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

/**
 * Enum representing the possible states of a support ticket.
 * 
 * States flow: open -> in_progress -> resolved -> closed
 */
enum TicketStatus: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';

    /**
     * Get all available status values as an array.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if the status can transition to another status.
     * Implements a simple state machine for ticket lifecycle.
     */
    public function canTransitionTo(TicketStatus $newStatus): bool
    {
        return match ($this) {
            self::OPEN => in_array($newStatus, [self::IN_PROGRESS, self::CLOSED], true),
            self::IN_PROGRESS => in_array($newStatus, [self::RESOLVED, self::OPEN, self::CLOSED], true),
            self::RESOLVED => in_array($newStatus, [self::CLOSED, self::IN_PROGRESS], true),
            self::CLOSED => false, // Closed tickets cannot transition
        };
    }

    /**
     * Get a human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::IN_PROGRESS => 'In Progress',
            self::RESOLVED => 'Resolved',
            self::CLOSED => 'Closed',
        };
    }
}
