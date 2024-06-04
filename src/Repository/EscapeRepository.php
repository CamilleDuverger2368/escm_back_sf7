<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\Entreprise;
use App\Entity\Escape;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Escape>
 *
 * @method Escape|null find($id, $lockMode = null, $lockVersion = null)
 * @method Escape|null findOneBy(array $criteria, array $orderBy = null)
 * @method Escape[]    findAll()
 * @method Escape[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EscapeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Escape::class);
    }

    /**
     * @param array{entreprises: array<Entreprise>, cities: array<City>, name: string} $search parameters for search
     *
     * @return array<Escape>
     */
    public function getEscapesByEntrepriseAndCityAndName(array $search)
    {
        $qb = $this->createQueryBuilder('e');

        if (count($search["entreprises"]) > 0) {
            $qb->andWhere(":entreprise MEMBER OF e.entreprises")
               ->setParameter("entreprise", $search["entreprises"][0]);
        }
        if (count($search["cities"]) > 0) {
            $qb->andWhere(":city MEMBER OF e.cities")
               ->setParameter("city", $search["cities"][0]);
        }
        if ($search["name"]) {
            $qb->andWhere("e.name LIKE :name")
               ->setParameter("name", '%' . $search["name"] . '%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Entreprise $entreprise
     * @param ?City $city
     * @param boolean $actual
     * @param boolean $unplayable
     *
     * @return array<Escape>
     */
    public function getEscapesOfEntrepriseByCity(Entreprise $entreprise, ?City $city, bool $actual, bool $unplayable)
    {
        $qb = $this->createQueryBuilder('e')
                   ->andWhere(":entreprise MEMBER OF e.entreprises")
                   ->setParameter("entreprise", $entreprise);

        if ($city) {
            $qb->andWhere(":city MEMBER OF e.cities")
               ->setParameter("city", $city);
        }
        if ($actual && !$unplayable) {
            $qb->andWhere("e.actual = 1");
        }
        if ($unplayable && !$actual) {
            $qb->andWhere("e.actual = 0");
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Entreprise $entreprise
     * @param City $city
     * @param array{nbPlayer: int, price: int, level: int, age: int, time: int, actual: boolean } $parameters
     *
     * @return array<Escape>
     */
    public function getEscapesByEntrepriseAndCity(Entreprise $entreprise, City $city, array $parameters)
    {
        $qb = $this->createQueryBuilder('e')
           ->andWhere(":entreprise MEMBER OF e.entreprises")
           ->andWhere(":city MEMBER OF e.cities")
           ->setParameter("entreprise", $entreprise)
           ->setParameter("city", $city);

        if ($parameters["nbPlayer"] > 0) {
            $qb->andWhere("e.minPlayer >= :nbPlayer")
                ->setParameter("nbPlayer", $parameters["nbPlayer"]);
        }
        if ($parameters["price"] > 0) {
            $qb->andWhere("e.price <= :price")
                ->setParameter("price", $parameters["price"]);
        }
        if ($parameters["level"] > 0) {
            $qb->andWhere("e.level = :level")
                ->setParameter("level", $parameters["level"]);
        }
        if ($parameters["age"]) {
            $qb->andWhere("e.age >= :age")
                ->setParameter("age", $parameters["age"]);
        }
        if ($parameters["time"]) {
            $qb->andWhere("e.time >= :time")
                ->setParameter("time", $parameters["time"]);
        }
        $parameters["actual"] === true ? $qb->andWhere("e.actual = 1") : $qb->andWhere("e.actual = 0");

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Tag $tag
     * @param City $city
     * @param array{nbPlayer: int, price: int, level: int, age: int, time: int, actual: boolean } $parameters
     *
     * @return array<Escape>
     */
    public function getEscapesByTagAndCity(Tag $tag, City $city, array $parameters)
    {
        $qb = $this->createQueryBuilder('e')
           ->andWhere(":tag MEMBER OF e.tags")
           ->andWhere(":city MEMBER OF e.cities")
           ->setParameter("tag", $tag)
           ->setParameter("city", $city);

        if ($parameters["nbPlayer"] > 0) {
            $qb->andWhere("e.minPlayer >= :nbPlayer")
                ->setParameter("nbPlayer", $parameters["nbPlayer"]);
        }
        if ($parameters["price"] > 0) {
            $qb->andWhere("e.price <= :price")
                ->setParameter("price", $parameters["price"]);
        }
        if ($parameters["level"] > 0) {
            $qb->andWhere("e.level = :level")
                ->setParameter("level", $parameters["level"]);
        }
        if ($parameters["age"]) {
            $qb->andWhere("e.age >= :age")
                ->setParameter("age", $parameters["age"]);
        }
        if ($parameters["time"]) {
            $qb->andWhere("e.time >= :time")
                ->setParameter("time", $parameters["time"]);
        }
        $parameters["actual"] === true ? $qb->andWhere("e.actual = 1") : $qb->andWhere("e.actual = 0");

        return $qb->getQuery()->getResult();
    }

//    /**
//     * @return Escape[] Returns an array of Escape objects
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

//    public function findOneBySomeField($value): ?Escape
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
