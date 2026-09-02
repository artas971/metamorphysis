<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

use App\Service\EncryptionService;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, UserLoaderInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private EncryptionService $encryptionService
    ) {
        parent::__construct($registry, User::class);
    }

    /**
     * Utilisé par Symfony Security pour authentifier un utilisateur par son identifiant / email.
     */
    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        $encrypted = $this->encryptionService->encrypt(mb_strtolower($identifier), true);

        return $this->createQueryBuilder('u')
            ->where('u.email = :enc OR u.email = :raw')
            ->setParameter('enc', $encrypted)
            ->setParameter('raw', $identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Recherche transparente par email (chiffré ou en clair).
     */
    public function findOneBy(array $criteria, ?array $orderBy = null): ?object
    {
        if (isset($criteria['email']) && is_string($criteria['email'])) {
            $email = $criteria['email'];
            if (!$this->encryptionService->isEncrypted($email)) {
                $criteria['email'] = $this->encryptionService->encrypt(mb_strtolower($email), true);
            }
            $result = parent::findOneBy($criteria, $orderBy);
            if (!$result) {
                // Fallback si l'email était encore stocké en clair
                $criteria['email'] = $email;
                $result = parent::findOneBy($criteria, $orderBy);
            }
            return $result;
        }

        return parent::findOneBy($criteria, $orderBy);
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        if (isset($criteria['email']) && is_string($criteria['email'])) {
            $email = $criteria['email'];
            if (!$this->encryptionService->isEncrypted($email)) {
                $criteria['email'] = $this->encryptionService->encrypt(mb_strtolower($email), true);
            }
            $results = parent::findBy($criteria, $orderBy, $limit, $offset);
            if (empty($results)) {
                $criteria['email'] = $email;
                $results = parent::findBy($criteria, $orderBy, $limit, $offset);
            }
            return $results;
        }

        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
