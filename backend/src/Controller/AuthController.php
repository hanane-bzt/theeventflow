<?php

namespace App\Controller;

use App\Entity\ConsentLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    #[Route('/api/auth/register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $required = ['email', 'password', 'firstName', 'lastName'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->json(['message' => "$field est obligatoire"], 400);
            }
        }

        if ($em->getRepository(User::class)->findOneBy(['email' => $data['email']])) {
            return $this->json(['message' => 'Email déjà utilisé'], 409);
        }

        $user = new User();
        $user
            ->setEmail($data['email'])
            ->setPassword($hasher->hashPassword($user, $data['password']))
            ->setFirstName($data['firstName'])
            ->setLastName($data['lastName'])
            ->setPhone($data['phone'] ?? null)
            ->setRole($data['role'] ?? 'USER')
            ->setConsentVersion('v1')
            ->setConsentDate(new \DateTime());

        $em->persist($user);

        $log = new ConsentLog();
        $log
            ->setUser($user)
            ->setAction('consent_given')
            ->setTimestamp(new \DateTime())
            ->setIpAddress(hash('sha256', $request->getClientIp() ?? 'unknown'));

        $em->persist($log);
        $em->flush();

        return $this->json(['message' => 'Utilisateur créé'], 201);
    }

    #[Route('/api/auth/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        if (empty($data['email']) || empty($data['password'])) {
            return $this->json(['message' => 'email et password sont obligatoires.'], 400);
        }

        $user = $entityManager->getRepository(User::class)->findOneBy([
            'email' => mb_strtolower(trim($data['email']))
        ]);

        if (!$user) {
            return $this->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        if (!$passwordHasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['message' => 'Mot de passe incorrect.'], 401);
        }

        return $this->json([
            'message' => 'Login OK',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'phone' => $user->getPhone(),
                'role' => $user->getRole(),
                'consentDate' => $user->getConsentDate()?->format('Y-m-d H:i:s'),
                'consentVersion' => $user->getConsentVersion(),
                'isAnonymized' => $user->isAnonymized(),
                'createdAt' => $user->getCreatedAt()?->format('Y-m-d H:i:s'),
            ]
        ]);
    }
}
