<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\Entreprise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Entreprise>
 *
 * @method Entreprise|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entreprise|null findOneBy(array $criteria, array $orderBy = null)
 * @method Entreprise[]    findAll()
 * @method Entreprise[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntrepriseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entreprise::class);
    }

    /**
     * @param array{name: string, cities: array<City>} $search
     *
     * @return array<Entreprise>
     */
    public function getEntreprisesByCityAndName(array $search)
    {
        $qb = $this->createQueryBuilder('e')
                   ->andWhere("e.name LIKE :name")
                   ->setParameter("name", '%' . $search["name"] . '%');

        if (count($search["cities"]) > 0) {
            $qb->andWhere(":city MEMBER OF e.cities")
                ->setParameter("city", $search["cities"][0]);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param City $city
     *
     * @return array<Entreprise>
     */
    public function getEntreprisesByCity(City $city)
    {
        return $this->createQueryBuilder('e')
                    ->andWhere(":city MEMBER OF e.cities")
                    ->setParameter("city", $city)
                    ->getQuery()
                    ->getResult()
        ;
    }

//    /**
//     * @return Entreprise[] Returns an array of Entreprise objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Entreprise
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
