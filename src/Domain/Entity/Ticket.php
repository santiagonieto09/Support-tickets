<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\InvalidTicketStatusTransitionException;
use App\Domain\ValueObject\TicketPriority;
use App\Domain\ValueObject\TicketStatus;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Ticket entity representing a support ticket in the system.
 * 
 * Each ticket belongs to a user and has a status and priority.
 */
#[ORM\Entity]
#[ORM\Table(name: 'tickets')]
#[ORM\HasLifecycleCallbacks]
class Ticket
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'string', length: 20, enumType: TicketStatus::class)]
    private TicketStatus $status;

    #[ORM\Column(type: 'string', length: 20, enumType: TicketPriority::class)]
    private TicketPriority $priority;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        string $title,
        string $description,
        User $user,
        TicketPriority $priority = TicketPriority::MEDIUM
    ) {
        $this->id = Uuid::v7();
        $this->title = $title;
        $this->description = $description;
        $this->user = $user;
        $this->priority = $priority;
        $this->status = TicketStatus::OPEN;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        // Bidirectional relationship
        $user->addTicket($this);
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        $this->touch();
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        $this->touch();
        return $this;
    }

    public function getStatus(): TicketStatus
    {
        return $this->status;
    }

    /**
     * Change the ticket status with validation.
     *
     * @throws InvalidTicketStatusTransitionException
     */
    public function setStatus(TicketStatus $newStatus): self
    {
        if ($this->status === $newStatus) {
            return $this;
        }

        if (!$this->status->canTransitionTo($newStatus)) {
            throw new InvalidTicketStatusTransitionException(
                $this->status,
                $newStatus
            );
        }

        $this->status = $newStatus;
        $this->touch();
        return $this;
    }

    public function getPriority(): TicketPriority
    {
        return $this->priority;
    }

    public function setPriority(TicketPriority $priority): self
    {
        $this->priority = $priority;
        $this->touch();
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Check if the ticket belongs to a specific user.
     */
    public function belongsTo(User $user): bool
    {
        return $this->user->getId()->equals($user->getId());
    }

    /**
     * Check if the ticket is open (can be worked on).
     */
    public function isOpen(): bool
    {
        return $this->status === TicketStatus::OPEN;
    }

    /**
     * Check if the ticket is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === TicketStatus::CLOSED;
    }

    /**
     * Update the updatedAt timestamp.
     */
    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
