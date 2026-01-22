<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\DTO\CreateTicketDTO;
use App\Application\DTO\UpdateTicketDTO;
use App\Application\Service\TicketService;
use App\Domain\Entity\Ticket;
use App\Domain\Entity\User;
use App\Domain\Exception\AccessDeniedException;
use App\Domain\Exception\TicketNotFoundException;
use App\Domain\Repository\TicketRepositoryInterface;
use App\Domain\ValueObject\TicketPriority;
use App\Domain\ValueObject\TicketStatus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Unit tests for TicketService.
 */
class TicketServiceTest extends TestCase
{
    private TicketRepositoryInterface&MockObject $ticketRepository;
    private TicketService $ticketService;
    private User $user;

    protected function setUp(): void
    {
        $this->ticketRepository = $this->createMock(TicketRepositoryInterface::class);
        $this->ticketService = new TicketService($this->ticketRepository);
        $this->user = new User('test@example.com', 'Test User');
    }

    public function testCreateTicket(): void
    {
        $dto = new CreateTicketDTO(
            title: 'Test Ticket',
            description: 'Test Description',
            priority: 'high'
        );

        $this->ticketRepository
            ->expects($this->once())
            ->method('save');

        $ticket = $this->ticketService->createTicket($dto, $this->user);

        $this->assertEquals('Test Ticket', $ticket->getTitle());
        $this->assertEquals('Test Description', $ticket->getDescription());
        $this->assertEquals(TicketPriority::HIGH, $ticket->getPriority());
        $this->assertEquals(TicketStatus::OPEN, $ticket->getStatus());
        $this->assertSame($this->user, $ticket->getUser());
    }

    public function testGetTicketsByUser(): void
    {
        $ticket1 = new Ticket('Ticket 1', 'Description 1', $this->user);
        $ticket2 = new Ticket('Ticket 2', 'Description 2', $this->user);

        $this->ticketRepository
            ->expects($this->once())
            ->method('findByUser')
            ->with($this->user)
            ->willReturn([$ticket1, $ticket2]);

        $tickets = $this->ticketService->getTicketsByUser($this->user);

        $this->assertCount(2, $tickets);
    }

    public function testGetTicketSuccess(): void
    {
        $ticket = new Ticket('Test Ticket', 'Description', $this->user);
        $ticketId = $ticket->getId()->toRfc4122();

        $this->ticketRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($ticket);

        $result = $this->ticketService->getTicket($ticketId, $this->user);

        $this->assertSame($ticket, $result);
    }

    public function testGetTicketNotFound(): void
    {
        $ticketId = Uuid::v7()->toRfc4122();

        $this->ticketRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn(null);

        $this->expectException(TicketNotFoundException::class);
        $this->ticketService->getTicket($ticketId, $this->user);
    }

    public function testGetTicketAccessDenied(): void
    {
        $otherUser = new User('other@example.com', 'Other User');
        $ticket = new Ticket('Test Ticket', 'Description', $otherUser);
        $ticketId = $ticket->getId()->toRfc4122();

        $this->ticketRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($ticket);

        $this->expectException(AccessDeniedException::class);
        $this->ticketService->getTicket($ticketId, $this->user);
    }

    public function testUpdateTicket(): void
    {
        $ticket = new Ticket('Original Title', 'Original Description', $this->user);
        $ticketId = $ticket->getId()->toRfc4122();

        $dto = new UpdateTicketDTO(
            title: 'Updated Title',
            description: 'Updated Description',
            priority: 'critical'
        );

        $this->ticketRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($ticket);

        $this->ticketRepository
            ->expects($this->once())
            ->method('save');

        $result = $this->ticketService->updateTicket($ticketId, $dto, $this->user);

        $this->assertEquals('Updated Title', $result->getTitle());
        $this->assertEquals('Updated Description', $result->getDescription());
        $this->assertEquals(TicketPriority::CRITICAL, $result->getPriority());
    }

    public function testUpdateTicketStatus(): void
    {
        $ticket = new Ticket('Title', 'Description', $this->user);
        $ticketId = $ticket->getId()->toRfc4122();

        $dto = new UpdateTicketDTO(status: 'in_progress');

        $this->ticketRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($ticket);

        $this->ticketRepository
            ->expects($this->once())
            ->method('save');

        $result = $this->ticketService->updateTicket($ticketId, $dto, $this->user);

        $this->assertEquals(TicketStatus::IN_PROGRESS, $result->getStatus());
    }

    public function testDeleteTicket(): void
    {
        $ticket = new Ticket('Test Ticket', 'Description', $this->user);
        $ticketId = $ticket->getId()->toRfc4122();

        $this->ticketRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($ticket);

        $this->ticketRepository
            ->expects($this->once())
            ->method('delete')
            ->with($ticket);

        $this->ticketService->deleteTicket($ticketId, $this->user);
    }

    public function testDeleteTicketNotFound(): void
    {
        $ticketId = Uuid::v7()->toRfc4122();

        $this->ticketRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn(null);

        $this->expectException(TicketNotFoundException::class);
        $this->ticketService->deleteTicket($ticketId, $this->user);
    }

    public function testDeleteTicketAccessDenied(): void
    {
        $otherUser = new User('other@example.com', 'Other User');
        $ticket = new Ticket('Test Ticket', 'Description', $otherUser);
        $ticketId = $ticket->getId()->toRfc4122();

        $this->ticketRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($ticket);

        $this->expectException(AccessDeniedException::class);
        $this->ticketService->deleteTicket($ticketId, $this->user);
    }

    public function testGetTicketWithInvalidUuidThrowsNotFoundException(): void
    {
        $this->expectException(TicketNotFoundException::class);
        $this->ticketService->getTicket('invalid-uuid', $this->user);
    }
}
