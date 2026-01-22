<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\ValueObject\TicketPriority;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for creating a new ticket.
 */
final readonly class CreateTicketDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Title is required')]
        #[Assert\Length(
            min: 5,
            max: 255,
            minMessage: 'Title must be at least {{ limit }} characters',
            maxMessage: 'Title cannot exceed {{ limit }} characters'
        )]
        public string $title,

        #[Assert\NotBlank(message: 'Description is required')]
        #[Assert\Length(
            min: 10,
            minMessage: 'Description must be at least {{ limit }} characters'
        )]
        public string $description,

        #[Assert\Choice(
            callback: [TicketPriority::class, 'values'],
            message: 'Invalid priority value'
        )]
        public string $priority = 'medium'
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
            title: $data['title'] ?? '',
            description: $data['description'] ?? '',
            priority: $data['priority'] ?? 'medium'
        );
    }

    public function getPriorityEnum(): TicketPriority
    {
        return TicketPriority::from($this->priority);
    }
}
