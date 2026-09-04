<?php

namespace App\Repository;

use App\Entity\SessionGroupe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SessionGroupe>
 */
class SessionGroupeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SessionGroupe::class);
    }

    /**
     * @return SessionGroupe[]
     */
    public function findSessionsAVenir(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.dateDebut > :now')
            ->andWhere('s.statut != :annule')
            ->setParameter('now', new \DateTime())
            ->setParameter('annule', 'Annulé')
            ->orderBy('s.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
