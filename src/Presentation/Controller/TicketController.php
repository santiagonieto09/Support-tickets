<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\DTO\CreateTicketDTO;
use App\Application\DTO\UpdateTicketDTO;
use App\Application\Service\TicketService;
use App\Domain\Entity\Ticket;
use App\Domain\Entity\User;
use App\Domain\Exception\AccessDeniedException;
use App\Domain\Exception\InvalidTicketStatusTransitionException;
use App\Domain\Exception\TicketNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * REST API Controller for ticket operations.
 */
#[Route('/api/tickets')]
class TicketController extends AbstractController
{
    public function __construct(
        private readonly TicketService $ticketService,
        private readonly ValidatorInterface $validator
    ) {
    }

    /**
     * List all tickets for the authenticated user.
     */
    #[Route('', name: 'api_tickets_list', methods: ['GET'])]
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $tickets = $this->ticketService->getTicketsByUser($user);

        return $this->json([
            'data' => array_map(
                fn(Ticket $ticket) => $this->serializeTicket($ticket),
                $tickets
            ),
            'total' => count($tickets),
        ]);
    }

    /**
     * Create a new ticket.
     */
    #[Route('', name: 'api_tickets_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();

            /** @var array<string, mixed> $data */
            $data = json_decode($request->getContent(), true) ?? [];
            $dto = CreateTicketDTO::fromArray($data);

            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                return $this->validationErrorResponse($errors);
            }

            $ticket = $this->ticketService->createTicket($dto, $user);

            return $this->json([
                'message' => 'Ticket created successfully',
                'data' => $this->serializeTicket($ticket),
            ], Response::HTTP_CREATED);
        } catch (\ValueError $e) {
            return $this->json([
                'error' => 'Invalid priority value',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get a specific ticket by ID.
     */
    #[Route('/{id}', name: 'api_tickets_show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            $ticket = $this->ticketService->getTicket($id, $user);

            return $this->json([
                'data' => $this->serializeTicket($ticket),
            ]);
        } catch (TicketNotFoundException $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (AccessDeniedException $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * Update an existing ticket.
     */
    #[Route('/{id}', name: 'api_tickets_update', methods: ['PUT', 'PATCH'])]
    public function update(string $id, Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();

            /** @var array<string, mixed> $data */
            $data = json_decode($request->getContent(), true) ?? [];
            $dto = UpdateTicketDTO::fromArray($data);

            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                return $this->validationErrorResponse($errors);
            }

            if (!$dto->hasChanges()) {
                return $this->json([
                    'error' => 'No changes provided',
                ], Response::HTTP_BAD_REQUEST);
            }

            $ticket = $this->ticketService->updateTicket($id, $dto, $user);

            return $this->json([
                'message' => 'Ticket updated successfully',
                'data' => $this->serializeTicket($ticket),
            ]);
        } catch (TicketNotFoundException $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (AccessDeniedException $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        } catch (InvalidTicketStatusTransitionException $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\ValueError $e) {
            return $this->json([
                'error' => 'Invalid status or priority value',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Delete a ticket.
     */
    #[Route('/{id}', name: 'api_tickets_delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            $this->ticketService->deleteTicket($id, $user);

            return $this->json([
                'message' => 'Ticket deleted successfully',
            ]);
        } catch (TicketNotFoundException $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (AccessDeniedException $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * Serialize a ticket entity to an array.
     *
     * @return array<string, mixed>
     */
    private function serializeTicket(Ticket $ticket): array
    {
        return [
            'id' => $ticket->getId()->toRfc4122(),
            'title' => $ticket->getTitle(),
            'description' => $ticket->getDescription(),
            'status' => $ticket->getStatus()->value,
            'status_label' => $ticket->getStatus()->label(),
            'priority' => $ticket->getPriority()->value,
            'priority_label' => $ticket->getPriority()->label(),
            'created_at' => $ticket->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updated_at' => $ticket->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ];
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
