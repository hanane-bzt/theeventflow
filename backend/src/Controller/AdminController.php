<?php

namespace App\Controller;

use App\Entity\ConsentLog;
use App\Entity\Event;
use App\Entity\Registration;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    //Événements

    #[Route('/api/admin/events', methods: ['GET'])]
    public function listEvents(EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $events = $em->getRepository(Event::class)->findBy([], ['createdAt' => 'DESC']);

        $data = array_map(function (Event $e) use ($em): array {
            $count = (int) $em->createQuery(
                'SELECT COUNT(r) FROM App\Entity\Registration r WHERE r.event = :e AND r.status != :c'
            )->setParameter('e', $e)->setParameter('c', 'cancelled')->getSingleScalarResult();

            $org = $e->getOrganizer();
            return [
                'id'                 => $e->getId(),
                'title'              => $e->getTitle(),
                'description'        => $e->getDescription(),
                'eventDate'          => $e->getEventDate()?->format(\DateTime::ATOM),
                'location'           => $e->getLocation(),
                'maxParticipants'    => $e->getMaxParticipants(),
                'isPublished'        => $e->isPublished(),
                'createdAt'          => $e->getCreatedAt()?->format(\DateTime::ATOM),
                'registrationsCount' => $count,
                'organizer'          => $org ? [
                    'id'        => $org->getId(),
                    'firstName' => $org->getFirstName(),
                    'lastName'  => $org->getLastName(),
                    'email'     => $org->getEmail(),
                ] : null,
            ];
        }, $events);

        return $this->json($data);
    }

    #[Route('/api/admin/events/{id}', methods: ['DELETE'])]
    public function deleteEvent(int $id, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $event = $em->getRepository(Event::class)->find($id);
        if (!$event) {
            return $this->json(['message' => 'Événement introuvable.'], 404);
        }

        foreach ($em->getRepository(Registration::class)->findBy(['event' => $event]) as $reg) {
            $em->remove($reg);
        }
        $em->remove($event);
        $em->flush();

        return $this->json(null, 204);
    }

    // Utilisateurs

    #[Route('/api/admin/users', methods: ['GET'])]
    public function listUsers(EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $em->getRepository(User::class)->findBy([], ['createdAt' => 'DESC']);

        $data = array_map(function (User $u) use ($em): array {
            $regCount = (int) $em->createQuery(
                'SELECT COUNT(r) FROM App\Entity\Registration r WHERE r.user = :u'
            )->setParameter('u', $u)->getSingleScalarResult();

            $evtCount = (int) $em->createQuery(
                'SELECT COUNT(e) FROM App\Entity\Event e WHERE e.organizer = :u'
            )->setParameter('u', $u)->getSingleScalarResult();

            return [
                'id'                 => $u->getId(),
                'email'              => $u->getEmail(),
                'firstName'          => $u->getFirstName(),
                'lastName'           => $u->getLastName(),
                'role'               => $u->getRole(),
                'isAnonymized'       => $u->isAnonymized(),
                'createdAt'          => $u->getCreatedAt()?->format(\DateTime::ATOM),
                'registrationsCount' => $regCount,
                'eventsCount'        => $evtCount,
            ];
        }, $users);

        return $this->json($data);
    }

    #[Route('/api/admin/users/{id}', methods: ['DELETE'])]
    public function deleteUser(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        $originalEmail = $user->getEmail() ?? '';
        $user
            ->setFirstName('Utilisateur supprimé')
            ->setLastName('Utilisateur supprimé')
            ->setEmail(hash('sha256', $originalEmail) . '@supprime.invalid')
            ->setPhone(null)
            ->setIsAnonymized(true);

        $log = new ConsentLog();
        $log->setUser($user)
            ->setAction('data_deleted')
            ->setTimestamp(new \DateTime())
            ->setIpAddress(hash('sha256', $request->getClientIp() ?? 'unknown'))
            ->setDetails('Anonymisation par un administrateur');

        $em->persist($log);
        $em->flush();

        return $this->json(null, 204);
    }
}
