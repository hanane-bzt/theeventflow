<?php

namespace App\Controller;

use App\Entity\ConsentLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class MeController extends AbstractController
{
    #[Route('/api/me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        return $this->json($this->getUser());
    }

    #[Route('/api/me', name: 'api_me_put', methods: ['PUT'])]
    public function updateMe(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find(1);

        if (!$user) {
            return $this->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        if (isset($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }

        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }

        if (array_key_exists('phone', $data)) {
            $user->setPhone($data['phone']);
        }

        $entityManager->flush();

        return $this->json(['message' => 'Données mises à jour avec succès.']);
    }

    #[Route('/api/me', name: 'api_me_delete', methods: ['DELETE'])]
    public function anonymizeMe(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find(1);

        if (!$user) {
            return $this->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        $originalEmail = $user->getEmail() ?? '';

        $user
            ->setFirstName('Utilisateur supprimé')
            ->setLastName('Utilisateur supprimé')
            ->setEmail(hash('sha256', $originalEmail))
            ->setPhone(null)
            ->setIsAnonymized(true);

        $log = new ConsentLog();
        $log
            ->setUser($user)
            ->setAction('data_deleted')
            ->setTimestamp(new \DateTime())
            ->setIpAddress(hash('sha256', $request->getClientIp() ?? 'unknown'))
            ->setDetails('Anonymisation du compte');

        $entityManager->persist($log);
        $entityManager->flush();

        return $this->json(['message' => 'Compte anonymisé avec succès.']);
    }
}