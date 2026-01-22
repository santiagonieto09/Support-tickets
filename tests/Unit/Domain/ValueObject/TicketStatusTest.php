<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\TicketStatus;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for TicketStatus enum.
 */
class TicketStatusTest extends TestCase
{
    public function testAllStatusValuesExist(): void
    {
        $expectedValues = ['open', 'in_progress', 'resolved', 'closed'];
        
        $this->assertEquals($expectedValues, TicketStatus::values());
    }

    public function testOpenCanTransitionToInProgress(): void
    {
        $status = TicketStatus::OPEN;
        
        $this->assertTrue($status->canTransitionTo(TicketStatus::IN_PROGRESS));
    }

    public function testOpenCanTransitionToClosed(): void
    {
        $status = TicketStatus::OPEN;
        
        $this->assertTrue($status->canTransitionTo(TicketStatus::CLOSED));
    }

    public function testOpenCannotTransitionToResolved(): void
    {
        $status = TicketStatus::OPEN;
        
        $this->assertFalse($status->canTransitionTo(TicketStatus::RESOLVED));
    }

    public function testInProgressCanTransitionToResolved(): void
    {
        $status = TicketStatus::IN_PROGRESS;
        
        $this->assertTrue($status->canTransitionTo(TicketStatus::RESOLVED));
    }

    public function testInProgressCanTransitionToOpen(): void
    {
        $status = TicketStatus::IN_PROGRESS;
        
        $this->assertTrue($status->canTransitionTo(TicketStatus::OPEN));
    }

    public function testResolvedCanTransitionToClosed(): void
    {
        $status = TicketStatus::RESOLVED;
        
        $this->assertTrue($status->canTransitionTo(TicketStatus::CLOSED));
    }

    public function testResolvedCanTransitionToInProgress(): void
    {
        $status = TicketStatus::RESOLVED;
        
        $this->assertTrue($status->canTransitionTo(TicketStatus::IN_PROGRESS));
    }

    public function testClosedCannotTransitionToAnyStatus(): void
    {
        $status = TicketStatus::CLOSED;
        
        $this->assertFalse($status->canTransitionTo(TicketStatus::OPEN));
        $this->assertFalse($status->canTransitionTo(TicketStatus::IN_PROGRESS));
        $this->assertFalse($status->canTransitionTo(TicketStatus::RESOLVED));
    }

    public function testLabelsAreCorrect(): void
    {
        $this->assertEquals('Open', TicketStatus::OPEN->label());
        $this->assertEquals('In Progress', TicketStatus::IN_PROGRESS->label());
        $this->assertEquals('Resolved', TicketStatus::RESOLVED->label());
        $this->assertEquals('Closed', TicketStatus::CLOSED->label());
    }
}
