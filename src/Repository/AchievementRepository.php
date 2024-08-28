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
    public function getAchievementsToUnlockedOfTypeByUser(string $type, User $user)
    {
        return $this->createQueryBuilder('a')
                    ->andWhere(":type = a.conditionType")
                    ->andWhere(":user NOT MEMBER OF a.users")
                    ->setParameter("type", $type)
                    ->setParameter("user", $user)
                    ->getQuery()
                    ->getResult()
        ;
    }

    /**
     * @param string $type achievement's type
     * @param User $user current user
     *
     * @return array<Achievement>
     */
    public function getUnlockedAchievementsOfTypeByUser(string $type, User $user)
    {
        return $this->createQueryBuilder('a')
                    ->andWhere(":type = a.conditionType")
                    ->andWhere(":user MEMBER OF a.users")
                    ->setParameter("type", $type)
                    ->setParameter("user", $user)
                    ->getQuery()
                    ->getResult()
        ;
    }

    /**
     * @param User $user current user
     *
     * @return array<Achievement>
     */
    public function getAchievementsToUnlocked(User $user)
    {
        return $this->createQueryBuilder('a')
                    ->andWhere(":user NOT MEMBER OF a.users")
                    ->setParameter("user", $user)
                    ->getQuery()
                    ->getResult()
        ;
    }

    /**
     * @param User $user current user
     *
     * @return array<Achievement>
     */
    public function getUnlockedAchievements(User $user)
    {
        return $this->createQueryBuilder('a')
                    ->andWhere(":user MEMBER OF a.users")
                    ->setParameter("user", $user)
                    ->getQuery()
                    ->getResult()
        ;
    }

    /**
     * @param User $user current user
     * @param string $element type of trophee
     *
     * @return array<Achievement>
     */
    public function getUnlockedElements(User $user, string $element)
    {
        return $this->createQueryBuilder('a')
                    ->select("a.trophee")
                    ->andWhere(":user MEMBER OF a.users")
                    ->andWhere(":trophee_type = a.tropheeType")
                    ->setParameter("user", $user)
                    ->setParameter("trophee_type", $element)
                    ->getQuery()
                    ->getResult()
        ;
    }

    /**
     * @param User $user current user
     *
     * @return array<Achievement>
     */
    public function getUnlockedObjects3D(User $user)
    {
        return $this->createQueryBuilder('a')
                    ->select("a.trophee")
                    ->andWhere(":user MEMBER OF a.users")
                    ->andWhere("'3D' = a.tropheeType")
                    ->setParameter("user", $user)
                    ->getQuery()
                    ->getResult()
        ;
    }

    /**
     * @param User $user current user
     *
     * @return array<Achievement>
     */
    public function getUnlockedTitltes(User $user)
    {
        return $this->createQueryBuilder('a')
                    ->select("a.trophee")
                    ->andWhere(":user MEMBER OF a.users")
                    ->andWhere("'title' = a.tropheeType")
                    ->setParameter("user", $user)
                    ->getQuery()
                    ->getResult()
        ;
    }

    /**
     * @param User $user current user
     *
     * @return array<Achievement>
     */
    public function getUnlockedPictures(User $user)
    {
        return $this->createQueryBuilder('a')
                    ->select("a.trophee")
                    ->andWhere(":user MEMBER OF a.users")
                    ->andWhere("'image' = a.tropheeType")
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
