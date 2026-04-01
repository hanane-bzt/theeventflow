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

        if (!isset($data['consent']) || !$data['consent']) {
            return $this->json(['message' => 'Le consentement est obligatoire.'], 422);
        }

        if ($em->getRepository(User::class)->findOneBy(['email' => mb_strtolower(trim($data['email']))])) {
            return $this->json(['message' => 'Cet email est déjà utilisé.'], 409);
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

        return $this->json(['message' => 'Compte créé avec succès.'], 201);
    }
}
