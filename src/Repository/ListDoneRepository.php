<?php

namespace App\Repository;

use App\Entity\Escape;
use App\Entity\ListDone;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListDone>
 *
 * @method ListDone|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListDone|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListDone[]    findAll()
 * @method ListDone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListDoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListDone::class);
    }

    /**
     * Get List done of an user
     *
     * @param User $user owner of list
     *
     * @return array<ListDone>
     */
    public function getByUser(User $user)
    {
        return $this->createQueryBuilder('l')
            ->andWhere(":user = l.user")
            ->setParameter("user", $user)
            ->getQuery()
            ->getResult();
    }

    public function isItAlreadyInList(User $user, Escape $escape): ?ListDone
    {
        return $this->createQueryBuilder('l')
            ->andWhere(":user = l.user")
            ->andWhere(":escape = l.escape")
            ->setParameter("user", $user)
            ->setParameter("escape", $escape)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return ListDone[] Returns an array of ListDone objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ListDone
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
