<?php

namespace App\Controller;

use App\Entity\ConsentLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ConsentController extends AbstractController
{
    #[Route('/api/consent', name: 'api_consent_post', methods: ['POST'])]
    public function updateConsent(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find(1);

        if (!$user) {
            return $this->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $action = $data['action'] ?? 'consent_given';
        $version = $data['consentVersion'] ?? 'v1';

        $user
            ->setConsentDate(new \DateTime())
            ->setConsentVersion($version);

        $log = new ConsentLog();
        $log
            ->setUser($user)
            ->setAction($action)
            ->setTimestamp(new \DateTime())
            ->setIpAddress(hash('sha256', $request->getClientIp() ?? 'unknown'))
            ->setDetails($data['details'] ?? 'Consent updated');

        $entityManager->persist($log);
        $entityManager->flush();

        return $this->json(['message' => 'Consentement mis à jour avec succès.']);
    }
}
