<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\RegisterUserDTO;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Service handling authentication-related operations.
 */
class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    /**
     * Register a new user.
     *
     * @throws \InvalidArgumentException if email is already registered
     */
    public function register(RegisterUserDTO $dto): User
    {
        if ($this->userRepository->emailExists($dto->email)) {
            throw new \InvalidArgumentException('Email is already registered');
        }

        $user = new User($dto->email, $dto->name);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->password);
        $user->setPassword($hashedPassword);

        $this->userRepository->save($user);

        return $user;
    }
}
