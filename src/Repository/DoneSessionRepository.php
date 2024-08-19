<?php

namespace App\Repository;

use App\Entity\DoneSession;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DoneSession>
 */
class DoneSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DoneSession::class);
    }

    /**
     * @param User $user
     *
     * @return array<DoneSession>
     */
    public function findSessions(User $user)
    {
        return $this->createQueryBuilder('d')
                    ->andWhere(":user MEMBER OF d.members")
                    ->setParameter("user", $user)
                    ->getQuery()
                    ->getResult()
        ;
    }

    //    /**
    //     * @return DoneSession[] Returns an array of DoneSession objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?DoneSession
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
