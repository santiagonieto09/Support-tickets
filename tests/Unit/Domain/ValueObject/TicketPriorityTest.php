<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\TicketPriority;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for TicketPriority enum.
 */
class TicketPriorityTest extends TestCase
{
    public function testAllPriorityValuesExist(): void
    {
        $expectedValues = ['low', 'medium', 'high', 'critical'];
        
        $this->assertEquals($expectedValues, TicketPriority::values());
    }

    public function testWeightsAreCorrect(): void
    {
        $this->assertEquals(1, TicketPriority::LOW->weight());
        $this->assertEquals(2, TicketPriority::MEDIUM->weight());
        $this->assertEquals(3, TicketPriority::HIGH->weight());
        $this->assertEquals(4, TicketPriority::CRITICAL->weight());
    }

    public function testLabelsAreCorrect(): void
    {
        $this->assertEquals('Low', TicketPriority::LOW->label());
        $this->assertEquals('Medium', TicketPriority::MEDIUM->label());
        $this->assertEquals('High', TicketPriority::HIGH->label());
        $this->assertEquals('Critical', TicketPriority::CRITICAL->label());
    }

    public function testCriticalIsHigherThanHigh(): void
    {
        $this->assertTrue(TicketPriority::CRITICAL->isHigherThan(TicketPriority::HIGH));
    }

    public function testHighIsHigherThanMedium(): void
    {
        $this->assertTrue(TicketPriority::HIGH->isHigherThan(TicketPriority::MEDIUM));
    }

    public function testMediumIsHigherThanLow(): void
    {
        $this->assertTrue(TicketPriority::MEDIUM->isHigherThan(TicketPriority::LOW));
    }

    public function testLowIsNotHigherThanMedium(): void
    {
        $this->assertFalse(TicketPriority::LOW->isHigherThan(TicketPriority::MEDIUM));
    }

    public function testSamePriorityIsNotHigher(): void
    {
        $this->assertFalse(TicketPriority::HIGH->isHigherThan(TicketPriority::HIGH));
    }
}
