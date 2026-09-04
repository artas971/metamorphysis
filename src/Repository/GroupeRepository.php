<?php

namespace App\Repository;

use App\Entity\Groupe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Groupe>
 */
class GroupeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Groupe::class);
    }

    public function findActifs(): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.statut = :statut')
            ->setParameter('statut', 'Actif')
            ->orderBy('g.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
