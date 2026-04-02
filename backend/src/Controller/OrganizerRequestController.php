<?php

namespace App\Controller;

use App\Entity\OrganizerRequest;
use App\Entity\User;
use App\Repository\OrganizerRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class OrganizerRequestController extends AbstractController
{
    #[Route('/api/organizer/request', methods: ['POST'])]
    public function createRequest(
        #[CurrentUser] ?User $user,
        OrganizerRequestRepository $requestRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        if ($user->isOrganizer() || $user->isAdmin()) {
            return $this->json(['message' => 'Ce compte a déjà les droits organisateur ou admin'], 400);
        }

        $existingPending = $requestRepository->findOneBy([
            'user' => $user,
            'status' => 'pending',
        ]);

        if ($existingPending) {
            return $this->json(['message' => 'Une demande est déjà en attente'], 400);
        }

        $requestEntity = new OrganizerRequest();
        $requestEntity->setUser($user);
        $requestEntity->setStatus('pending');

        $em->persist($requestEntity);
        $em->flush();

        return $this->json([
            'message' => 'Demande envoyée avec succès',
            'request' => [
                'id' => $requestEntity->getId(),
                'status' => $requestEntity->getStatus(),
                'createdAt' => $requestEntity->getCreatedAt()?->format('Y-m-d H:i:s'),
            ],
        ], 201);
    }

    #[Route('/api/admin/organizer-requests', methods: ['GET'])]
    public function listRequests(
        OrganizerRequestRepository $requestRepository
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $requests = $requestRepository->findBy([], ['createdAt' => 'DESC']);

        $data = array_map(function (OrganizerRequest $request) {
            $user = $request->getUser();

            return [
                'id' => $request->getId(),
                'status' => $request->getStatus(),
                'createdAt' => $request->getCreatedAt()?->format('Y-m-d H:i:s'),
                'reviewedAt' => $request->getReviewedAt()?->format('Y-m-d H:i:s'),
                'adminComment' => $request->getAdminComment(),
                'user' => [
                    'id' => $user?->getId(),
                    'email' => $user?->getEmail(),
                    'firstName' => $user?->getFirstName(),
                    'lastName' => $user?->getLastName(),
                    'role' => $user?->getRole(),
                ],
            ];
        }, $requests);

        return $this->json($data);
    }

    #[Route('/api/admin/organizer-requests/{id}/approve', methods: ['POST'])]
    public function approveRequest(
        int $id,
        Request $request,
        OrganizerRequestRepository $requestRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $organizerRequest = $requestRepository->find($id);

        if (!$organizerRequest) {
            return $this->json(['message' => 'Demande introuvable'], 404);
        }

        if ($organizerRequest->getStatus() !== 'pending') {
            return $this->json(['message' => 'Seules les demandes en attente peuvent être validées'], 400);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $organizerRequest->setStatus('approved');
        $organizerRequest->setReviewedAt(new \DateTime());
        $organizerRequest->setAdminComment($data['adminComment'] ?? null);

        $user = $organizerRequest->getUser();
        if ($user) {
            $user->setRole('ORGANIZER');
        }

        $em->flush();

        return $this->json([
            'message' => 'Demande approuvée',
            'user' => [
                'id' => $user?->getId(),
                'email' => $user?->getEmail(),
                'role' => $user?->getRole(),
            ],
        ]);
    }

    #[Route('/api/admin/organizer-requests/{id}/reject', methods: ['POST'])]
    public function rejectRequest(
        int $id,
        Request $request,
        OrganizerRequestRepository $requestRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $organizerRequest = $requestRepository->find($id);

        if (!$organizerRequest) {
            return $this->json(['message' => 'Demande introuvable'], 404);
        }

        if ($organizerRequest->getStatus() !== 'pending') {
            return $this->json(['message' => 'Seules les demandes en attente peuvent être refusées'], 400);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $organizerRequest->setStatus('rejected');
        $organizerRequest->setReviewedAt(new \DateTime());
        $organizerRequest->setAdminComment($data['adminComment'] ?? null);

        $em->flush();

        return $this->json([
            'message' => 'Demande refusée',
            'request' => [
                'id' => $organizerRequest->getId(),
                'status' => $organizerRequest->getStatus(),
                'adminComment' => $organizerRequest->getAdminComment(),
            ],
        ]);
    }

    #[Route('/api/me/organizer-requests', methods: ['GET'])]
    public function myRequests(
        #[CurrentUser] ?User $user,
        OrganizerRequestRepository $requestRepository
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        $requests = $requestRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        $data = array_map(function (OrganizerRequest $request) {
            return [
                'id' => $request->getId(),
                'status' => $request->getStatus(),
                'createdAt' => $request->getCreatedAt()?->format('Y-m-d H:i:s'),
                'reviewedAt' => $request->getReviewedAt()?->format('Y-m-d H:i:s'),
                'adminComment' => $request->getAdminComment(),
            ];
        }, $requests);

        return $this->json($data);
    }
}