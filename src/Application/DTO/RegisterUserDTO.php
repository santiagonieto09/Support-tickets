<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for user registration.
 */
final readonly class RegisterUserDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email(message: 'Invalid email format')]
        public string $email,

        #[Assert\NotBlank(message: 'Password is required')]
        #[Assert\Length(
            min: 8,
            minMessage: 'Password must be at least {{ limit }} characters'
        )]
        public string $password,

        #[Assert\NotBlank(message: 'Name is required')]
        #[Assert\Length(
            min: 2,
            max: 100,
            minMessage: 'Name must be at least {{ limit }} characters',
            maxMessage: 'Name cannot exceed {{ limit }} characters'
        )]
        public string $name
    ) {
    }

    /**
     * Create DTO from request data array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'] ?? '',
            password: $data['password'] ?? '',
            name: $data['name'] ?? ''
        );
    }
}
