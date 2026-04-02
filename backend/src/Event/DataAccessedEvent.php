<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Événement déclenché chaque fois qu'un utilisateur accède ou modifie ses données personnelles.
 * Utilisé par RgpdSubscriber pour créer automatiquement un ConsentLog (traçabilité RGPD).
 */
class DataAccessedEvent extends Event
{
    public const NAME = 'rgpd.data_accessed';

    public function __construct(
        private readonly User    $user,
        private readonly string  $action,
        private readonly string  $ipAddress,
        private readonly ?string $details = null,
    ) {}

    public function getUser(): User
    {
        return $this->user;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }
}
