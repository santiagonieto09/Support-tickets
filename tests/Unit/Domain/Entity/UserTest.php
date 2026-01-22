<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Unit tests for User entity.
 */
class UserTest extends TestCase
{
    public function testUserCreation(): void
    {
        $user = new User('test@example.com', 'John Doe');

        $this->assertInstanceOf(Uuid::class, $user->getId());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('John Doe', $user->getName());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
    }

    public function testUserIdentifierIsEmail(): void
    {
        $user = new User('test@example.com', 'John Doe');

        $this->assertEquals('test@example.com', $user->getUserIdentifier());
    }

    public function testUserHasDefaultRole(): void
    {
        $user = new User('test@example.com', 'John Doe');

        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testSetEmail(): void
    {
        $user = new User('test@example.com', 'John Doe');
        $user->setEmail('new@example.com');

        $this->assertEquals('new@example.com', $user->getEmail());
    }

    public function testSetName(): void
    {
        $user = new User('test@example.com', 'John Doe');
        $user->setName('Jane Doe');

        $this->assertEquals('Jane Doe', $user->getName());
    }

    public function testSetPassword(): void
    {
        $user = new User('test@example.com', 'John Doe');
        $user->setPassword('hashed_password');

        $this->assertEquals('hashed_password', $user->getPassword());
    }

    public function testSetRoles(): void
    {
        $user = new User('test@example.com', 'John Doe');
        $user->setRoles(['ROLE_ADMIN']);

        $roles = $user->getRoles();
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles); // Always includes ROLE_USER
    }

    public function testTicketsCollectionIsEmpty(): void
    {
        $user = new User('test@example.com', 'John Doe');

        $this->assertCount(0, $user->getTickets());
    }
}
