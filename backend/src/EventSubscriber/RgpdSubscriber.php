<?php

namespace App\EventSubscriber;

use App\Entity\ConsentLog;
use App\Event\DataAccessedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber RGPD : écoute les événements DataAccessedEvent et trace chaque accès
 * ou modification de données personnelles dans la table consent_logs.
 *
 * Conformément à l'article 30 du RGPD (registre des activités de traitement),
 * chaque consultation ou modification de données personnelles est enregistrée avec :
 * - l'utilisateur concerné
 * - l'action effectuée (data_accessed, data_modified, data_deleted)
 * - l'horodatage
 * - l'adresse IP pseudonymisée (SHA-256)
 */
class RgpdSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public static function getSubscribedEvents(): array
    {
        return [
            DataAccessedEvent::NAME => 'onDataAccessed',
        ];
    }

    public function onDataAccessed(DataAccessedEvent $event): void
    {
        $log = new ConsentLog();
        $log
            ->setUser($event->getUser())
            ->setAction($event->getAction())
            ->setTimestamp(new \DateTime())
            ->setIpAddress(hash('sha256', $event->getIpAddress()))
            ->setDetails($event->getDetails());

        $this->em->persist($log);
        $this->em->flush();
    }
}
