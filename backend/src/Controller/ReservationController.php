<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\EventRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ReservationController extends AbstractController
{
    #[Route('/api/reservations', name: 'api_reservations_list', methods: ['GET'])]
    public function list(ReservationRepository $reservationRepository): JsonResponse
    {
        $user = $this->getUser();
        $reservations = $reservationRepository->findBy(['user' => $user], ['id' => 'DESC']);

        $data = array_map(fn (Reservation $reservation) => [
            'id' => $reservation->getId(),
            'quantity' => $reservation->getQuantity(),
            'createdAt' => $reservation->getCreatedAt()->format(DATE_ATOM),
            'event' => [
                'id' => $reservation->getEvent()?->getId(),
                'title' => $reservation->getEvent()?->getTitle(),
                'location' => $reservation->getEvent()?->getLocation(),
                'eventDate' => $reservation->getEvent()?->getEventDate()->format(DATE_ATOM),
            ],
        ], $reservations);

        return $this->json($data);
    }

    #[Route('/api/reservations', name: 'api_reservations_create', methods: ['POST'])]
    public function create(
        Request $request,
        EventRepository $eventRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        if (empty($data['eventId'])) {
            return $this->json(['message' => 'eventId est obligatoire.'], 400);
        }

        $event = $eventRepository->find($data['eventId']);
        if (!$event) {
            return $this->json(['message' => 'Événement introuvable.'], 404);
        }

        $reservation = (new Reservation())
            ->setUser($this->getUser())
            ->setEvent($event)
            ->setQuantity(max(1, (int) ($data['quantity'] ?? 1)));

        $entityManager->persist($reservation);
        $entityManager->flush();

        return $this->json(['message' => 'Réservation créée.', 'id' => $reservation->getId()], 201);
    }

    #[Route('/api/reservations/{id}', name: 'api_reservations_delete', methods: ['DELETE'])]
    public function delete(Reservation $reservation, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($reservation->getUser()?->getId() !== $this->getUser()?->getId()) {
            return $this->json(['message' => 'Accès interdit.'], 403);
        }

        $entityManager->remove($reservation);
        $entityManager->flush();

        return $this->json(['message' => 'Réservation supprimée.']);
    }
}