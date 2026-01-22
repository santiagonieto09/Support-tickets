<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Ticket;
use App\Domain\Entity\User;
use Symfony\Component\Uid\Uuid;

/**
 * Interface for Ticket repository operations.
 * 
 * Following the Repository Pattern, this interface defines the contract
 * for data access operations on Ticket entities.
 */
interface TicketRepositoryInterface
{
    /**
     * Find a ticket by its ID.
     */
    public function findById(Uuid $id): ?Ticket;

    /**
     * Find all tickets belonging to a specific user.
     *
     * @return array<Ticket>
     */
    public function findByUser(User $user): array;

    /**
     * Find all tickets (admin operation).
     *
     * @return array<Ticket>
     */
    public function findAll(): array;

    /**
     * Save a ticket (create or update).
     */
    public function save(Ticket $ticket): void;

    /**
     * Delete a ticket.
     */
    public function delete(Ticket $ticket): void;
}
