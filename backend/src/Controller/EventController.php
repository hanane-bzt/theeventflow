<?php

namespace App\Controller;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class EventController extends AbstractController
{
    #[Route('/api/events', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $events = $em->getRepository(Event::class)->findBy(['isPublished' => true]);

        return $this->json($events);
    }

    #[Route('/api/events/{id}', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em): JsonResponse
    {
        $event = $em->getRepository(Event::class)->find($id);

        if (!$event) {
            return $this->json(['message' => 'Not found'], 404);
        }

        return $this->json($event);
    }

    #[Route('/api/events', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ORGANIZER');

        $data = json_decode($request->getContent(), true);

        $event = new Event();
        $event
            ->setTitle($data['title'])
            ->setDescription($data['description'])
            ->setEventDate(new \DateTime($data['eventDate']))
            ->setLocation($data['location'])
            ->setMaxParticipants($data['maxParticipants'])
            ->setOrganizer($this->getUser())
            ->setIsPublished(true);

        $em->persist($event);
        $em->flush();

        return $this->json(['message' => 'Event créé'], 201);
    }

    #[Route('/api/events/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $event = $em->getRepository(Event::class)->find($id);

        if (!$event) {
            return $this->json(['message' => 'Not found'], 404);
        }

        if ($event->getOrganizer() !== $this->getUser()) {
            return $this->json(['message' => 'Forbidden'], 403);
        }

        $data = json_decode($request->getContent(), true);

        $event->setTitle($data['title']);

        $em->flush();

        return $this->json(['message' => 'Updated']);
    }

    #[Route('/api/events/{id}', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $event = $em->getRepository(Event::class)->find($id);

        if ($event->getOrganizer() !== $this->getUser()) {
            return $this->json(['message' => 'Forbidden'], 403);
        }

        $em->remove($event);
        $em->flush();

        return $this->json(['message' => 'Deleted']);
    }
}