<?php

namespace App\Repository;

use App\Entity\Room;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Room>
 *
 * @method Room|null find($id, $lockMode = null, $lockVersion = null)
 * @method Room|null findOneBy(array $criteria, array $orderBy = null)
 * @method Room[]    findAll()
 * @method Room[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Room::class);
    }

    /**
     * @param User $user
     *
     * @return array<Room>
     */
    public function getRoomWhereUserIsMember(User $user)
    {
        return $this->createQueryBuilder('r')
                    ->andWhere(":user MEMBER OF r.members")
                    ->setParameter("user", $user)
                    ->getQuery()
                    ->getResult()
        ;
    }

    /**
     * @param User $user
     * @param array<int, User> $members
     *
     * @return array<Room>
     */
    public function getRoomsWhereMembersAre(User $user, array $members)
    {
        $qb = $this->createQueryBuilder('r')
                    ->andWhere(":user MEMBER OF r.members")
                    ->setParameter("user", $user);
        foreach ($members as $member) {
            $qb->andWhere(":member MEMBER OF r.members")
               ->setParameter("member", $member);
        }

        return $qb->getQuery()->getResult();
    }

//    /**
//     * @return Room[] Returns an array of Room objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Room
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
