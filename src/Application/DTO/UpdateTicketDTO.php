<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\ValueObject\TicketPriority;
use App\Domain\ValueObject\TicketStatus;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for updating an existing ticket.
 */
final readonly class UpdateTicketDTO
{
    public function __construct(
        #[Assert\Length(
            min: 5,
            max: 255,
            minMessage: 'Title must be at least {{ limit }} characters',
            maxMessage: 'Title cannot exceed {{ limit }} characters'
        )]
        public ?string $title = null,

        #[Assert\Length(
            min: 10,
            minMessage: 'Description must be at least {{ limit }} characters'
        )]
        public ?string $description = null,

        #[Assert\Choice(
            callback: [TicketStatus::class, 'values'],
            message: 'Invalid status value'
        )]
        public ?string $status = null,

        #[Assert\Choice(
            callback: [TicketPriority::class, 'values'],
            message: 'Invalid priority value'
        )]
        public ?string $priority = null
    ) {
    }

    /**
     * Create DTO from request data array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            status: $data['status'] ?? null,
            priority: $data['priority'] ?? null
        );
    }

    public function getStatusEnum(): ?TicketStatus
    {
        return $this->status !== null ? TicketStatus::from($this->status) : null;
    }

    public function getPriorityEnum(): ?TicketPriority
    {
        return $this->priority !== null ? TicketPriority::from($this->priority) : null;
    }

    public function hasChanges(): bool
    {
        return $this->title !== null
            || $this->description !== null
            || $this->status !== null
            || $this->priority !== null;
    }
}
