<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\Description;
use App\Entity\Entreprise;
use App\Entity\Escape;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Description>
 *
 * @method Description|null find($id, $lockMode = null, $lockVersion = null)
 * @method Description|null findOneBy(array $criteria, array $orderBy = null)
 * @method Description[]    findAll()
 * @method Description[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DescriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Description::class);
    }

    public function getDescriptionByCityAndEscapeAndEntreprise(
        City $city,
        Escape $escape,
        Entreprise $entreprise
    ): ?Description {
        return $this->createQueryBuilder('d')
                    ->andWhere(":city = d.city")
                    ->andWhere(":escape = d.escape")
                    ->andWhere(":entreprise = d.entreprise")
                    ->setParameter("city", $city)
                    ->setParameter("escape", $escape)
                    ->setParameter("entreprise", $entreprise)
                    ->getQuery()
                    ->getOneOrNullResult()
        ;
    }

    /**
     * Get ecape's descriptions from a city
     *
     * @param City $city user's city
     * @param Escape $escape escape to find
     *
     * @return array<Description>
     */
    public function getDescriptionByCityAndEscape(City $city, Escape $escape)
    {
        return $this->createQueryBuilder('d')
                    ->andWhere(":city = d.city")
                    ->andWhere(":escape = d.escape")
                    ->setParameter("city", $city)
                    ->setParameter("escape", $escape)
                    ->getQuery()
                    ->getResult()
        ;
    }

//    /**
//     * @return Description[] Returns an array of Description objects
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

//    public function findOneBySomeField($value): ?Description
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
