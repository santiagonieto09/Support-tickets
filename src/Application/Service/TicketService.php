<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\CreateTicketDTO;
use App\Application\DTO\UpdateTicketDTO;
use App\Domain\Entity\Ticket;
use App\Domain\Entity\User;
use App\Domain\Exception\AccessDeniedException;
use App\Domain\Exception\TicketNotFoundException;
use App\Domain\Repository\TicketRepositoryInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Service handling ticket-related business logic.
 * 
 * This service acts as the application layer, orchestrating
 * domain operations and enforcing business rules.
 */
class TicketService
{
    public function __construct(
        private readonly TicketRepositoryInterface $ticketRepository
    ) {
    }

    /**
     * Create a new ticket for a user.
     */
    public function createTicket(CreateTicketDTO $dto, User $user): Ticket
    {
        $ticket = new Ticket(
            title: $dto->title,
            description: $dto->description,
            user: $user,
            priority: $dto->getPriorityEnum()
        );

        $this->ticketRepository->save($ticket);

        return $ticket;
    }

    /**
     * Get all tickets for a specific user.
     *
     * @return array<Ticket>
     */
    public function getTicketsByUser(User $user): array
    {
        return $this->ticketRepository->findByUser($user);
    }

    /**
     * Get a specific ticket by ID, ensuring the user has access.
     *
     * @throws TicketNotFoundException
     * @throws AccessDeniedException
     */
    public function getTicket(string $id, User $user): Ticket
    {
        $ticket = $this->findTicketOrFail($id);
        $this->ensureUserOwnsTicket($ticket, $user);

        return $ticket;
    }

    /**
     * Update an existing ticket.
     *
     * @throws TicketNotFoundException
     * @throws AccessDeniedException
     */
    public function updateTicket(string $id, UpdateTicketDTO $dto, User $user): Ticket
    {
        $ticket = $this->findTicketOrFail($id);
        $this->ensureUserOwnsTicket($ticket, $user);

        if ($dto->title !== null) {
            $ticket->setTitle($dto->title);
        }

        if ($dto->description !== null) {
            $ticket->setDescription($dto->description);
        }

        if ($dto->status !== null) {
            $ticket->setStatus($dto->getStatusEnum());
        }

        if ($dto->priority !== null) {
            $ticket->setPriority($dto->getPriorityEnum());
        }

        $this->ticketRepository->save($ticket);

        return $ticket;
    }

    /**
     * Delete a ticket.
     *
     * @throws TicketNotFoundException
     * @throws AccessDeniedException
     */
    public function deleteTicket(string $id, User $user): void
    {
        $ticket = $this->findTicketOrFail($id);
        $this->ensureUserOwnsTicket($ticket, $user);

        $this->ticketRepository->delete($ticket);
    }

    /**
     * Find a ticket by ID or throw an exception.
     *
     * @throws TicketNotFoundException
     */
    private function findTicketOrFail(string $id): Ticket
    {
        try {
            $uuid = Uuid::fromString($id);
        } catch (\InvalidArgumentException) {
            throw new TicketNotFoundException($id);
        }

        $ticket = $this->ticketRepository->findById($uuid);

        if ($ticket === null) {
            throw new TicketNotFoundException($id);
        }

        return $ticket;
    }

    /**
     * Ensure the user owns the ticket.
     *
     * @throws AccessDeniedException
     */
    private function ensureUserOwnsTicket(Ticket $ticket, User $user): void
    {
        if (!$ticket->belongsTo($user)) {
            throw new AccessDeniedException('You do not have access to this ticket');
        }
    }
}
