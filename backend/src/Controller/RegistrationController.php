<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Registration;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    private function serializeRegistration(Registration $reg, bool $withEvent = false): array
    {
        $data = [
            'id'           => $reg->getId(),
            'userId'       => $reg->getUser()?->getId(),
            'eventId'      => $reg->getEvent()?->getId(),
            'registeredAt' => $reg->getRegisteredAt()?->format(\DateTime::ATOM),
            'status'       => $reg->getStatus(),
        ];

        if ($withEvent && $reg->getEvent()) {
            $event = $reg->getEvent();
            $data['event'] = [
                'id'             => $event->getId(),
                'title'          => $event->getTitle(),
                'eventDate'      => $event->getEventDate()?->format(\DateTime::ATOM),
                'location'       => $event->getLocation(),
                'maxParticipants' => $event->getMaxParticipants(),
                'isPublished'    => $event->isPublished(),
            ];
        }

        return $data;
    }

    #[Route('/api/me/registrations', methods: ['GET'])]
    public function myRegistrations(EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $registrations = $em->getRepository(Registration::class)->findBy(
            ['user' => $user],
            ['registeredAt' => 'DESC']
        );

        $data = array_map(fn(Registration $r) => $this->serializeRegistration($r, true), $registrations);

        return $this->json($data);
    }

    #[Route('/api/events/{id}/my-registration', methods: ['GET'])]
    public function myRegistrationForEvent(int $id, EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $event = $em->getRepository(Event::class)->find($id);
        if (!$event) {
            return $this->json(null);
        }

        $reg = $em->getRepository(Registration::class)->findOneBy([
            'user'  => $user,
            'event' => $event,
        ]);

        if (!$reg || $reg->getStatus() === 'cancelled') {
            return $this->json(null);
        }

        return $this->json($this->serializeRegistration($reg));
    }

    #[Route('/api/events/{id}/register', methods: ['POST'])]
    public function register(int $id, EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $event = $em->getRepository(Event::class)->find($id);

        if (!$event) {
            return $this->json(['message' => 'Événement introuvable.'], 404);
        }

        // Check for existing active registration
        $existing = $em->getRepository(Registration::class)->findOneBy([
            'user'  => $user,
            'event' => $event,
        ]);

        if ($existing && $existing->getStatus() !== 'cancelled') {
            return $this->json(['message' => 'Vous êtes déjà inscrit à cet événement.'], 422);
        }

        // Check capacity
        $activeCount = (int) $em->createQuery(
            'SELECT COUNT(r) FROM App\Entity\Registration r WHERE r.event = :event AND r.status != :cancelled'
        )
            ->setParameter('event', $event)
            ->setParameter('cancelled', 'cancelled')
            ->getSingleScalarResult();

        if ($activeCount >= $event->getMaxParticipants()) {
            return $this->json(['message' => 'Cet événement est complet.'], 422);
        }

        if ($existing) {
            // Re-register after cancellation
            $existing->setStatus('confirmed');
            $existing->setRegisteredAt(new \DateTime());
            $em->flush();
            return $this->json($this->serializeRegistration($existing), 201);
        }

        $registration = new Registration();
        $registration
            ->setUser($user)
            ->setEvent($event)
            ->setStatus('confirmed');

        $em->persist($registration);
        $em->flush();

        return $this->json($this->serializeRegistration($registration), 201);
    }

    #[Route('/api/registrations/{id}', methods: ['DELETE'])]
    public function cancel(int $id, EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $registration = $em->getRepository(Registration::class)->find($id);

        if (!$registration) {
            return $this->json(['message' => 'Inscription introuvable.'], 404);
        }

        if ($registration->getUser() !== $user) {
            return $this->json(['message' => 'Forbidden'], 403);
        }

        $registration->setStatus('cancelled');
        $em->flush();

        return $this->json(null, 204);
    }
}
