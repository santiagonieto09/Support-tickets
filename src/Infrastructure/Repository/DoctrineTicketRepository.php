<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Ticket;
use App\Domain\Entity\User;
use App\Domain\Repository\TicketRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Doctrine implementation of TicketRepositoryInterface.
 */
class DoctrineTicketRepository implements TicketRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function findById(Uuid $id): ?Ticket
    {
        return $this->entityManager->getRepository(Ticket::class)->find($id);
    }

    /**
     * @return array<Ticket>
     */
    public function findByUser(User $user): array
    {
        return $this->entityManager->getRepository(Ticket::class)
            ->findBy(
                ['user' => $user],
                ['createdAt' => 'DESC']
            );
    }

    /**
     * @return array<Ticket>
     */
    public function findAll(): array
    {
        return $this->entityManager->getRepository(Ticket::class)
            ->findBy([], ['createdAt' => 'DESC']);
    }

    public function save(Ticket $ticket): void
    {
        $this->entityManager->persist($ticket);
        $this->entityManager->flush();
    }

    public function delete(Ticket $ticket): void
    {
        $this->entityManager->remove($ticket);
        $this->entityManager->flush();
    }
}
