<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/api/events', name: 'api_events_list', methods: ['GET'])]
    public function list(EventRepository $eventRepository): JsonResponse
    {
        $events = $eventRepository->findBy([], ['eventDate' => 'ASC']);
        $data = array_map(fn (Event $event) => [
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'description' => $event->getDescription(),
            'location' => $event->getLocation(),
            'eventDate' => $event->getEventDate()->format(DATE_ATOM),
            'capacity' => $event->getCapacity(),
            'imageUrl' => $event->getImageUrl(),
        ], $events);

        return $this->json($data);
    }

    #[Route('/api/events/{id}', name: 'api_events_show', methods: ['GET'])]
    public function show(Event $event): JsonResponse
    {
        return $this->json([
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'description' => $event->getDescription(),
            'location' => $event->getLocation(),
            'eventDate' => $event->getEventDate()->format(DATE_ATOM),
            'capacity' => $event->getCapacity(),
            'imageUrl' => $event->getImageUrl(),
        ]);
    }

    #[Route('/api/events', name: 'api_events_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $data = json_decode($request->getContent(), true) ?? [];

        foreach (['title', 'description', 'location', 'eventDate', 'capacity'] as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                return $this->json(['message' => sprintf('%s est obligatoire.', $field)], 400);
            }
        }

        $event = (new Event())
            ->setTitle($data['title'])
            ->setDescription($data['description'])
            ->setLocation($data['location'])
            ->setEventDate(new \DateTimeImmutable($data['eventDate']))
            ->setCapacity((int) $data['capacity'])
            ->setImageUrl($data['imageUrl'] ?? null);

        $entityManager->persist($event);
        $entityManager->flush();

        $entityManager->flush();

        return $this->json(['message' => 'Événement mis à jour.']);
    }

    #[Route('/api/events/{id}', name: 'api_events_delete', methods: ['DELETE'])]
    public function delete(Event $event, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $entityManager->remove($event);
        $entityManager->flush();

        return $this->json(['message' => 'Événement supprimé.']);
    }
}

        