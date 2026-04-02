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
    /**
     * POST /api/consent — mise à jour du consentement RGPD de l'utilisateur connecté.
     * Enregistre un ConsentLog avec l'IP pseudonymisée (SHA-256).
     */
    #[Route('/api/consent', name: 'api_consent_post', methods: ['POST'])]
    public function updateConsent(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true) ?? [];

        $granted = (bool) ($data['granted'] ?? true);
        $action  = $granted ? 'consent_given' : 'consent_withdrawn';
        $version = $data['consentVersion'] ?? 'v1';

        if ($granted) {
            $user
                ->setConsentDate(new \DateTime())
                ->setConsentVersion($version);
        }

        $log = new ConsentLog();
        $log
            ->setUser($user)
            ->setAction($action)
            ->setTimestamp(new \DateTime())
            ->setIpAddress(hash('sha256', $request->getClientIp() ?? 'unknown'))
            ->setDetails($data['details'] ?? null);

        $entityManager->persist($log);
        $entityManager->flush();

        return $this->json(['message' => $granted ? 'Consentement accordé.' : 'Consentement retiré.']);
    }
}

