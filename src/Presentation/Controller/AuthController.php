<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\DTO\RegisterUserDTO;
use App\Application\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Controller handling authentication endpoints.
 */
#[Route('/api')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly ValidatorInterface $validator
    ) {
    }

    /**
     * Register a new user.
     */
    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        try {
            /** @var array<string, mixed> $data */
            $data = json_decode($request->getContent(), true) ?? [];
            $dto = RegisterUserDTO::fromArray($data);

            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                return $this->validationErrorResponse($errors);
            }

            $user = $this->authService->register($dto);

            return $this->json([
                'message' => 'User registered successfully',
                'user' => [
                    'id' => $user->getId()->toRfc4122(),
                    'email' => $user->getEmail(),
                    'name' => $user->getName(),
                ],
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_CONFLICT);
        }
    }

    /**
     * Convert validation errors to JSON response.
     *
     * @param \Symfony\Component\Validator\ConstraintViolationListInterface<mixed> $errors
     */
    private function validationErrorResponse($errors): JsonResponse
    {
        $messages = [];
        foreach ($errors as $error) {
            $messages[$error->getPropertyPath()] = $error->getMessage();
        }

        return $this->json([
            'error' => 'Validation failed',
            'details' => $messages,
        ], Response::HTTP_BAD_REQUEST);
    }
}
