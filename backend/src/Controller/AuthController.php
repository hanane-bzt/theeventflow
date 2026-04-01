<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
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
            'email' => $data['email']
        ]);

        if (!$user) {
            return $this->json(['message' => 'Utilisateur introuvable'], 404);
        }

        if (!$passwordHasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['message' => 'Mot de passe incorrect'], 401);
        }

        return $this->json([
            'message' => 'Login OK',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'fullName' => $user->getFullName(),
                'roles' => $user->getRoles(),
            ]
        ]);
    }
    #[Route('/api/auth/register', name: 'api_auth_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        if (empty($data['email']) || empty($data['password']) || empty($data['fullName'])) {
            return $this->json(['message' => 'email, password et fullName sont obligatoires.'], 400);
        }
        $user = new User();
        $user
            ->setEmail($data['email'])
            ->setFullName($data['fullName'])
            ->setPassword($passwordHasher->hashPassword($user, $data['password']))
            ->setRoles(['ROLE_USER']);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'message' => 'Utilisateur créé avec succès.',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'fullName' => $user->getFullName(),
                'roles' => $user->getRoles(),
            ],
        ], 201);
    }
}