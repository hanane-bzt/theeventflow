<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Registration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/api/events/{id}/register', methods: ['POST'])]
    public function register(int $id, EntityManagerInterface $em): JsonResponse
    {
        $event = $em->getRepository(Event::class)->find($id);

        $registration = new Registration();
        $registration
            ->setUser($this->getUser())
            ->setEvent($event)
            ->setStatus('confirmed');

        $em->persist($registration);
        $em->flush();

        return $this->json(['message' => 'Registered']);
    }

    #[Route('/api/registrations/{id}', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $registration = $em->getRepository(Registration::class)->find($id);

        if ($registration->getUser() !== $this->getUser()) {
            return $this->json(['message' => 'Forbidden'], 403);
        }

        $registration->setStatus('cancelled');
        $em->flush();

        return $this->json(['message' => 'Cancelled']);
    }
}
