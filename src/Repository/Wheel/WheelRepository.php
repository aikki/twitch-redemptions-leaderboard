<?php

namespace App\Repository\Wheel;

use App\Entity\Wheel\Wheel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wheel>
 *
 * @method Wheel|null find($id, $lockMode = null, $lockVersion = null)
 * @method Wheel|null findOneBy(array $criteria, array $orderBy = null)
 * @method Wheel[]    findAll()
 * @method Wheel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WheelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wheel::class);
    }

//    /**
//     * @return Wheel[] Returns an array of Wheel objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('w.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Wheel
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
