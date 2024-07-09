<?php

namespace App\Repository;

use App\Entity\Achievement;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Achievement>
 */
class AchievementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Achievement::class);
    }

    /**
     * @param string $type achievement's type
     * @param User $user current user
     *
     * @return array<Achievement>
     */
    public function getAchievementsUnlockedOfTypeByUser(string $type, User $user)
    {
        return $this->createQueryBuilder('a')
                    ->andWhere(":type = a.conditionType")
                    ->andWhere(":user !=MEMBER OF a.users")
                    ->setParameter("type", $type)
                    ->setParameter("user", $user)
                    ->getQuery()
                    ->getResult()
        ;
    }

    //    /**
    //     * @return Achievement[] Returns an array of Achievement objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Achievement
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
