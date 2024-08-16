<?php

namespace App\Repository;

use App\Entity\Friendship;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Friendship>
 */
class FriendshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friendship::class);
    }

    /**
     * @param User $sender user
     * @param User $receiver user
     *
     * @return Friendship
     */
    public function searchStatusFriendship(User $sender, User $receiver)
    {
        return $this->createQueryBuilder('f')
                   ->andWhere("f.sender = :sender OR f.sender = :receiver")
                   ->andWhere("f.receiver = :receiver OR f.receiver = :sender")
                   ->setParameter("sender", $sender)
                   ->setParameter("receiver", $receiver)
                   ->getQuery()
                   ->getOneOrNullResult();
    }

    /**
     * @param User $user user
     *
     * @return array<Friendship>
     */
    public function getAllFriendships(User $user)
    {
        return $this->createQueryBuilder('f')
                   ->andWhere("f.friend = 1")
                   ->andWhere("f.sender = :user OR f.receiver = :user")
                   ->setParameter("user", $user)
                   ->getQuery()
                   ->getResult();
    }

    /**
     * @param User $user user
     *
     * @return array<Friendship>
     */
    public function getAllRequests(User $user)
    {
        return $this->createQueryBuilder('f')
                   ->andWhere("f.friend = 0")
                   ->andWhere("f.sender = :user OR f.receiver = :user")
                   ->setParameter("user", $user)
                   ->getQuery()
                   ->getResult();
    }

    //    /**
    //     * @return Friendship[] Returns an array of Friendship objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Friendship
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
