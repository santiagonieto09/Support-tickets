<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\DTO;

use App\Application\DTO\CreateTicketDTO;
use App\Domain\ValueObject\TicketPriority;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for CreateTicketDTO.
 */
class CreateTicketDTOTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'priority' => 'high',
        ];

        $dto = CreateTicketDTO::fromArray($data);

        $this->assertEquals('Test Ticket', $dto->title);
        $this->assertEquals('Test Description', $dto->description);
        $this->assertEquals('high', $dto->priority);
    }

    public function testCreateFromArrayWithDefaults(): void
    {
        $data = [
            'title' => 'Test Ticket',
            'description' => 'Test Description',
        ];

        $dto = CreateTicketDTO::fromArray($data);

        $this->assertEquals('medium', $dto->priority);
    }

    public function testGetPriorityEnum(): void
    {
        $dto = new CreateTicketDTO(
            title: 'Test',
            description: 'Description',
            priority: 'critical'
        );

        $this->assertEquals(TicketPriority::CRITICAL, $dto->getPriorityEnum());
    }

    public function testCreateFromEmptyArray(): void
    {
        $dto = CreateTicketDTO::fromArray([]);

        $this->assertEquals('', $dto->title);
        $this->assertEquals('', $dto->description);
        $this->assertEquals('medium', $dto->priority);
    }
}
