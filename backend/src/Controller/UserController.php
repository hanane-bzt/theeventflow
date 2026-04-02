<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/api/users/me', name: 'api_users_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        return $this->json([
            'id' => $user?->getId(),
            'email' => $user?->getEmail(),
            'fullName' => $user?->getFullName(),
            'roles' => $user?->getRoles(),
        ]);
    }

    #[Route('/api/users', methods: ['GET'])]
    public function getUsers(UserRepository $repo): JsonResponse
    {
        $users = $repo->findAll();

        return $this->json($users);
    }
}