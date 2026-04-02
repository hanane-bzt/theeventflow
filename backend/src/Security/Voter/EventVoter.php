<?php

namespace App\Security\Voter;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter pour les actions sur les événements.
 * Vérifie que seul l'organisateur propriétaire peut modifier ou supprimer son événement.
 */
class EventVoter extends Voter
{
    public const EDIT   = 'EVENT_EDIT';
    public const DELETE = 'EVENT_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE], true)
            && $subject instanceof Event;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // l'utilisateur doit être connecté
        if (!$user instanceof User) {
            return false;
        }

        /** @var Event $event */
        $event = $subject;

        return match ($attribute) {
            self::EDIT, self::DELETE => $event->getOrganizer() === $user,
            default                  => false,
        };
    }
}
