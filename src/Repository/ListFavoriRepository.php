<?php

namespace App\Repository;

use App\Entity\Escape;
use App\Entity\ListFavori;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListFavori>
 *
 * @method ListFavori|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListFavori|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListFavori[]    findAll()
 * @method ListFavori[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListFavoriRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListFavori::class);
    }

    /**
     * Get List favori of an user
     *
     * @param User $user owner of list
     *
     * @return array<ListFavori>
     */
    public function getByUser(User $user)
    {
        return $this->createQueryBuilder('l')
            ->andWhere(":user = l.user")
            ->setParameter("user", $user)
            ->getQuery()
            ->getResult();
    }

    public function isItAlreadyInList(User $user, Escape $escape): ?ListFavori
    {
        return $this->createQueryBuilder('l')
                    ->andWhere(":user = l.user")
                    ->andWhere(":escape = l.escape")
                    ->setParameter("user", $user)
                    ->setParameter("escape", $escape)
                    ->getQuery()
                    ->getOneOrNullResult()
        ;
    }
//    /**
//     * @return ListFavori[] Returns an array of ListFavori objects
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

//    public function findOneBySomeField($value): ?ListFavori
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
