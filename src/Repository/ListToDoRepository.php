<?php

namespace App\Repository;

use App\Entity\Escape;
use App\Entity\ListToDo;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListToDo>
 *
 * @method ListToDo|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListToDo|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListToDo[]    findAll()
 * @method ListToDo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListToDoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListToDo::class);
    }

    /**
     * Get List to-do of an user
     *
     * @param User $user owner of list
     *
     * @return array<ListToDo>
     */
    public function getByUser(User $user)
    {
        return $this->createQueryBuilder('l')
            ->andWhere(":user = l.user")
            ->setParameter("user", $user)
            ->getQuery()
            ->getResult();
    }

    public function isItAlreadyInList(User $user, Escape $escape): ?ListToDo
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
    //     * @return ListToDo[] Returns an array of ListToDo objects
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

    //    public function findOneBySomeField($value): ?ListToDo
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
