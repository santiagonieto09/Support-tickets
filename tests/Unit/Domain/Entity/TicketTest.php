<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\Ticket;
use App\Domain\Entity\User;
use App\Domain\Exception\InvalidTicketStatusTransitionException;
use App\Domain\ValueObject\TicketPriority;
use App\Domain\ValueObject\TicketStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Unit tests for Ticket entity.
 */
class TicketTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User('test@example.com', 'John Doe');
    }

    public function testTicketCreation(): void
    {
        $ticket = new Ticket(
            'Test Ticket',
            'This is a test description',
            $this->user
        );

        $this->assertInstanceOf(Uuid::class, $ticket->getId());
        $this->assertEquals('Test Ticket', $ticket->getTitle());
        $this->assertEquals('This is a test description', $ticket->getDescription());
        $this->assertEquals(TicketStatus::OPEN, $ticket->getStatus());
        $this->assertEquals(TicketPriority::MEDIUM, $ticket->getPriority());
        $this->assertSame($this->user, $ticket->getUser());
    }

    public function testTicketCreationWithCustomPriority(): void
    {
        $ticket = new Ticket(
            'Test Ticket',
            'This is a test description',
            $this->user,
            TicketPriority::CRITICAL
        );

        $this->assertEquals(TicketPriority::CRITICAL, $ticket->getPriority());
    }

    public function testSetTitle(): void
    {
        $ticket = new Ticket('Original Title', 'Description', $this->user);
        $ticket->setTitle('Updated Title');

        $this->assertEquals('Updated Title', $ticket->getTitle());
    }

    public function testSetDescription(): void
    {
        $ticket = new Ticket('Title', 'Original Description', $this->user);
        $ticket->setDescription('Updated Description');

        $this->assertEquals('Updated Description', $ticket->getDescription());
    }

    public function testSetPriority(): void
    {
        $ticket = new Ticket('Title', 'Description', $this->user);
        $ticket->setPriority(TicketPriority::HIGH);

        $this->assertEquals(TicketPriority::HIGH, $ticket->getPriority());
    }

    public function testValidStatusTransition(): void
    {
        $ticket = new Ticket('Title', 'Description', $this->user);
        
        // OPEN -> IN_PROGRESS (valid)
        $ticket->setStatus(TicketStatus::IN_PROGRESS);
        $this->assertEquals(TicketStatus::IN_PROGRESS, $ticket->getStatus());
    }

    public function testInvalidStatusTransitionThrowsException(): void
    {
        $ticket = new Ticket('Title', 'Description', $this->user);
        
        // OPEN -> RESOLVED (invalid)
        $this->expectException(InvalidTicketStatusTransitionException::class);
        $ticket->setStatus(TicketStatus::RESOLVED);
    }

    public function testSameStatusTransitionDoesNotThrow(): void
    {
        $ticket = new Ticket('Title', 'Description', $this->user);
        
        // OPEN -> OPEN (same status, should not throw)
        $ticket->setStatus(TicketStatus::OPEN);
        $this->assertEquals(TicketStatus::OPEN, $ticket->getStatus());
    }

    public function testBelongsTo(): void
    {
        $ticket = new Ticket('Title', 'Description', $this->user);

        $this->assertTrue($ticket->belongsTo($this->user));
    }

    public function testBelongsToReturnsFalseForDifferentUser(): void
    {
        $ticket = new Ticket('Title', 'Description', $this->user);
        $otherUser = new User('other@example.com', 'Other User');

        $this->assertFalse($ticket->belongsTo($otherUser));
    }

    public function testIsOpen(): void
    {
        $ticket = new Ticket('Title', 'Description', $this->user);

        $this->assertTrue($ticket->isOpen());
    }

    public function testIsOpenReturnsFalseWhenInProgress(): void
    {
        $ticket = new Ticket('Title', 'Description', $this->user);
        $ticket->setStatus(TicketStatus::IN_PROGRESS);

        $this->assertFalse($ticket->isOpen());
    }

    public function testIsClosed(): void
    {
        $ticket = new Ticket('Title', 'Description', $this->user);
        $ticket->setStatus(TicketStatus::CLOSED);

        $this->assertTrue($ticket->isClosed());
    }

    public function testTicketIsAddedToUserCollection(): void
    {
        $ticket = new Ticket('Title', 'Description', $this->user);

        $this->assertContains($ticket, $this->user->getTickets());
    }

    public function testTimestampsAreSet(): void
    {
        $ticket = new Ticket('Title', 'Description', $this->user);

        $this->assertInstanceOf(\DateTimeImmutable::class, $ticket->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $ticket->getUpdatedAt());
    }
}
