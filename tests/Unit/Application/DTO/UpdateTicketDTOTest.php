<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\DTO;

use App\Application\DTO\UpdateTicketDTO;
use App\Domain\ValueObject\TicketPriority;
use App\Domain\ValueObject\TicketStatus;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for UpdateTicketDTO.
 */
class UpdateTicketDTOTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'status' => 'in_progress',
            'priority' => 'high',
        ];

        $dto = UpdateTicketDTO::fromArray($data);

        $this->assertEquals('Updated Title', $dto->title);
        $this->assertEquals('Updated Description', $dto->description);
        $this->assertEquals('in_progress', $dto->status);
        $this->assertEquals('high', $dto->priority);
    }

    public function testCreateFromPartialArray(): void
    {
        $data = [
            'title' => 'Updated Title',
        ];

        $dto = UpdateTicketDTO::fromArray($data);

        $this->assertEquals('Updated Title', $dto->title);
        $this->assertNull($dto->description);
        $this->assertNull($dto->status);
        $this->assertNull($dto->priority);
    }

    public function testHasChangesReturnsTrueWhenTitleProvided(): void
    {
        $dto = new UpdateTicketDTO(title: 'New Title');

        $this->assertTrue($dto->hasChanges());
    }

    public function testHasChangesReturnsTrueWhenStatusProvided(): void
    {
        $dto = new UpdateTicketDTO(status: 'closed');

        $this->assertTrue($dto->hasChanges());
    }

    public function testHasChangesReturnsFalseWhenEmpty(): void
    {
        $dto = new UpdateTicketDTO();

        $this->assertFalse($dto->hasChanges());
    }

    public function testGetStatusEnum(): void
    {
        $dto = new UpdateTicketDTO(status: 'resolved');

        $this->assertEquals(TicketStatus::RESOLVED, $dto->getStatusEnum());
    }

    public function testGetStatusEnumReturnsNullWhenNotSet(): void
    {
        $dto = new UpdateTicketDTO();

        $this->assertNull($dto->getStatusEnum());
    }

    public function testGetPriorityEnum(): void
    {
        $dto = new UpdateTicketDTO(priority: 'critical');

        $this->assertEquals(TicketPriority::CRITICAL, $dto->getPriorityEnum());
    }

    public function testGetPriorityEnumReturnsNullWhenNotSet(): void
    {
        $dto = new UpdateTicketDTO();

        $this->assertNull($dto->getPriorityEnum());
    }
}
