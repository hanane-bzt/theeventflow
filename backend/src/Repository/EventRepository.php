<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * Retourne les événements publiés à venir, triés par date croissante.
     *
     * @return Event[]
     */
    public function findUpcomingPublished(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.isPublished = true')
            ->andWhere('e.eventDate >= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('e.eventDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les événements organisés par un utilisateur donné, triés par date de création décroissante.
     *
     * @return Event[]
     */
    public function findByOrganizer(User $organizer): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.organizer = :organizer')
            ->setParameter('organizer', $organizer)
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
