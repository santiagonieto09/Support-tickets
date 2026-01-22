<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\User;
use Symfony\Component\Uid\Uuid;

/**
 * Interface for User repository operations.
 */
interface UserRepositoryInterface
{
    /**
     * Find a user by their ID.
     */
    public function findById(Uuid $id): ?User;

    /**
     * Find a user by their email address.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Save a user (create or update).
     */
    public function save(User $user): void;

    /**
     * Check if an email is already registered.
     */
    public function emailExists(string $email): bool;
}
