<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

/**
 * Enum representing the priority levels of a support ticket.
 */
enum TicketPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    /**
     * Get all available priority values as an array.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get the numeric weight of the priority for sorting purposes.
     * Higher number = higher priority.
     */
    public function weight(): int
    {
        return match ($this) {
            self::LOW => 1,
            self::MEDIUM => 2,
            self::HIGH => 3,
            self::CRITICAL => 4,
        };
    }

    /**
     * Get a human-readable label for the priority.
     */
    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
            self::CRITICAL => 'Critical',
        };
    }

    /**
     * Check if this priority is higher than another.
     */
    public function isHigherThan(TicketPriority $other): bool
    {
        return $this->weight() > $other->weight();
    }
}
