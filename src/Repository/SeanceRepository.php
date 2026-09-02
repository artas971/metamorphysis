<?php

namespace App\Repository;

use App\Entity\Seance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Seance>
 */
class SeanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Seance::class);
    }
    // src/Repository/SeanceRepository.php

    /**
     * Récupère les séances confirmées qui ont lieu dans les prochaines 47 à 49 heures (Fenêtre 48h).
     */
    public function findSeancesIn48Hours(): array
    {
        $maintenant = new \DateTime();
        
        $debut = (clone $maintenant)->modify('+47 hours');
        $fin = (clone $maintenant)->modify('+49 hours');

        return $this->createQueryBuilder('s')
            ->where('s.dateRendezVous >= :debut')
            ->andWhere('s.dateRendezVous < :fin')
            ->andWhere('s.statut = :statut') // On ne rappelle que les séances confirmées
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->setParameter('statut', 'Confirmé')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les séances confirmées qui ont lieu dans les prochaines 24 à 25 heures.
     */
    public function findSeancesIn24Hours(): array
    {
        $maintenant = new \DateTime();
        
        $debut = (clone $maintenant)->modify('+24 hours');
        $fin = (clone $maintenant)->modify('+25 hours');

        return $this->createQueryBuilder('s')
            ->where('s.dateRendezVous >= :debut')
            ->andWhere('s.dateRendezVous < :fin')
            ->andWhere('s.statut = :statut') // On ne rappelle que les séances confirmées
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->setParameter('statut', 'Confirmé')
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return Seance[] Returns an array of Seance objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Seance
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
