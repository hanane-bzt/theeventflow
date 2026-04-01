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
    private function serializeUser(User $user): array
    {
        return [
            'id'              => $user->getId(),
            'email'           => $user->getEmail(),
            'firstName'       => $user->getFirstName(),
            'lastName'        => $user->getLastName(),
            'phone'           => $user->getPhone(),
            'role'            => $user->getRole(),
            'roles'           => $user->getRoles(),
            'consentDate'     => $user->getConsentDate()?->format(\DateTime::ATOM),
            'consentVersion'  => $user->getConsentVersion(),
            'isAnonymized'    => $user->isAnonymized(),
            'createdAt'       => $user->getCreatedAt()?->format(\DateTime::ATOM),
        ];
    }

    #[Route('/api/me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        return $this->json($this->serializeUser($user));
    }

    #[Route('/api/me', name: 'api_me_put', methods: ['PUT'])]
    public function updateMe(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

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

        return $this->json($this->serializeUser($user));
    }

    #[Route('/api/me', name: 'api_me_delete', methods: ['DELETE'])]
    public function anonymizeMe(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $originalEmail = $user->getEmail() ?? '';

        $user
            ->setFirstName('Utilisateur supprimé')
            ->setLastName('Utilisateur supprimé')
            ->setEmail(hash('sha256', $originalEmail) . '@supprime.invalid')
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

    #[Route('/api/me/consent', name: 'api_me_consent', methods: ['PUT'])]
    public function updateConsent(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true) ?? [];
        $granted = (bool) ($data['granted'] ?? false);

        if ($granted) {
            $user->setConsentDate(new \DateTime());
            $user->setConsentVersion('v1');
        }

        $log = new ConsentLog();
        $log
            ->setUser($user)
            ->setAction($granted ? 'consent_given' : 'consent_withdrawn')
            ->setTimestamp(new \DateTime())
            ->setIpAddress(hash('sha256', $request->getClientIp() ?? 'unknown'));

        $entityManager->persist($log);
        $entityManager->flush();

        return $this->json(['message' => $granted ? 'Consentement accordé.' : 'Consentement retiré.']);
    }
}
