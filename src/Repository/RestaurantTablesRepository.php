<?php

namespace App\Repository;

use App\Entity\RestaurantTables;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Restaurants;

/**
 * @method RestaurantTables|null find($id, $lockMode = null, $lockVersion = null)
 * @method RestaurantTables|null findOneBy(array $criteria, array $orderBy = null)
 * @method RestaurantTables[]    findAll()
 * @method RestaurantTables[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RestaurantTablesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RestaurantTables::class);
    }

    // /**
    //  * @return RestaurantTables[] Returns an array of RestaurantTables objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RestaurantTables
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findActiveById($id)
    {
        return $this->createQueryBuilder('q')
            ->select('count(q.id)')
            ->where('q.restaurantId = :id AND q.status = true ')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findAllById($id)
    {
        return $this->createQueryBuilder('q')
            ->select('q')
            ->where('q.restaurantId = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }
}
