<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Registration;
use App\Entity\User;
use App\Security\Voter\EventVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EventController extends AbstractController
{
    public function __construct(private readonly ValidatorInterface $validator) {}
    private function serializeEvent(Event $event, int $registrationsCount): array
    {
        $organizer = $event->getOrganizer();
        return [
            'id'                 => $event->getId(),
            'title'              => $event->getTitle(),
            'description'        => $event->getDescription(),
            'eventDate'          => $event->getEventDate()?->format(\DateTime::ATOM),
            'location'           => $event->getLocation(),
            'maxParticipants'    => $event->getMaxParticipants(),
            'organizer'          => $organizer ? [
                'id'        => $organizer->getId(),
                'firstName' => $organizer->getFirstName(),
                'lastName'  => $organizer->getLastName(),
            ] : null,
            'isPublished'        => $event->isPublished(),
            'createdAt'          => $event->getCreatedAt()?->format(\DateTime::ATOM),
            'registrationsCount' => $registrationsCount,
        ];
    }

    private function countRegistrations(Event $event, EntityManagerInterface $em): int
    {
        return (int) $em->createQuery(
            'SELECT COUNT(r) FROM App\Entity\Registration r WHERE r.event = :event AND r.status != :cancelled'
        )
            ->setParameter('event', $event)
            ->setParameter('cancelled', 'cancelled')
            ->getSingleScalarResult();
    }

    #[Route('/api/events', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $events = $em->getRepository(Event::class)->findBy(['isPublished' => true], ['createdAt' => 'DESC']);

        $data = array_map(
            fn(Event $e) => $this->serializeEvent($e, $this->countRegistrations($e, $em)),
            $events
        );

        return $this->json($data);
    }

    #[Route('/api/events/{id}', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em): JsonResponse
    {
        $event = $em->getRepository(Event::class)->find($id);

        if (!$event) {
            return $this->json(['message' => 'Événement introuvable.'], 404);
        }

        return $this->json($this->serializeEvent($event, $this->countRegistrations($event, $em)));
    }

    #[Route('/api/me/events', methods: ['GET'])]
    public function myEvents(EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ORGANIZER');

        /** @var User $user */
        $user = $this->getUser();
        $events = $em->getRepository(Event::class)->findBy(['organizer' => $user], ['createdAt' => 'DESC']);

        $data = array_map(
            fn(Event $e) => $this->serializeEvent($e, $this->countRegistrations($e, $em)),
            $events
        );

        return $this->json($data);
    }

    #[Route('/api/events', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ORGANIZER');

        $data = json_decode($request->getContent(), true) ?? [];

        /** @var User $user */
        $user = $this->getUser();

        $event = new Event();
        $event
            ->setTitle($data['title'] ?? '')
            ->setDescription($data['description'] ?? '')
            ->setLocation($data['location'] ?? '')
            ->setMaxParticipants((int) ($data['maxParticipants'] ?? 1))
            ->setOrganizer($user)
            ->setIsPublished((bool) ($data['isPublished'] ?? false));

        if (!empty($data['eventDate'])) {
            $event->setEventDate(new \DateTime($data['eventDate']));
        }

        $errors = $this->validator->validate($event);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $messages], 422);
        }

        $em->persist($event);
        $em->flush();

        return $this->json($this->serializeEvent($event, 0), 201);
    }

    #[Route('/api/events/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $event = $em->getRepository(Event::class)->find($id);

        if (!$event) {
            return $this->json(['message' => 'Événement introuvable.'], 404);
        }

        // le Voter vérifie que l'utilisateur connecté est bien le propriétaire
        $this->denyAccessUnlessGranted(EventVoter::EDIT, $event);

        $data = json_decode($request->getContent(), true) ?? [];

        if (isset($data['title']))           $event->setTitle($data['title']);
        if (isset($data['description']))     $event->setDescription($data['description']);
        if (isset($data['eventDate']))       $event->setEventDate(new \DateTime($data['eventDate']));
        if (isset($data['location']))        $event->setLocation($data['location']);
        if (isset($data['maxParticipants'])) $event->setMaxParticipants((int) $data['maxParticipants']);
        if (isset($data['isPublished']))     $event->setIsPublished((bool) $data['isPublished']);

        $errors = $this->validator->validate($event);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $messages], 422);
        }

        $em->flush();

        return $this->json($this->serializeEvent($event, $this->countRegistrations($event, $em)));
    }

    #[Route('/api/events/{id}', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $event = $em->getRepository(Event::class)->find($id);

        if (!$event) {
            return $this->json(['message' => 'Événement introuvable.'], 404);
        }

        // le Voter vérifie que l'utilisateur connecté est bien le propriétaire
        $this->denyAccessUnlessGranted(EventVoter::DELETE, $event);

        $em->remove($event);
        $em->flush();

        return $this->json(null, 204);
    }
}
