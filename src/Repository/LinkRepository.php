<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\Entreprise;
use App\Entity\Escape;
use App\Entity\Link;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Link>
 *
 * @method Link|null find($id, $lockMode = null, $lockVersion = null)
 * @method Link|null findOneBy(array $criteria, array $orderBy = null)
 * @method Link[]    findAll()
 * @method Link[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Link::class);
    }

    public function getLinkByCityAndEscapeAndEntreprise(City $city, Escape $escape, Entreprise $entreprise): ?Link
    {
        return $this->createQueryBuilder('l')
                    ->andWhere(":city = l.city")
                    ->andWhere(":escape = l.escape")
                    ->andWhere(":entreprise = l.entreprise")
                    ->setParameter("city", $city)
                    ->setParameter("escape", $escape)
                    ->setParameter("entreprise", $entreprise)
                    ->getQuery()
                    ->getOneOrNullResult()
        ;
    }

    /**
     * Get ecape's links from a city
     *
     * @param City $city user's city
     * @param Escape $escape escape to find
     *
     * @return array<Link>
     */
    public function getLinkByCityAndEscape(City $city, Escape $escape)
    {
        return $this->createQueryBuilder('l')
                    ->andWhere(":city = l.city")
                    ->andWhere(":escape = l.escape")
                    ->setParameter("city", $city)
                    ->setParameter("escape", $escape)
                    ->getQuery()
                    ->getResult()
        ;
    }

//    /**
//     * @return Link[] Returns an array of Link objects
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

//    public function findOneBySomeField($value): ?Link
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
