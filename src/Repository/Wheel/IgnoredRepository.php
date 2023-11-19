<?php

namespace App\Repository\Wheel;

use App\Entity\Wheel\Ignored;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ignored>
 *
 * @method Ignored|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ignored|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ignored[]    findAll()
 * @method Ignored[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IgnoredRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ignored::class);
    }

//    /**
//     * @return Ignored[] Returns an array of Ignored objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Ignored
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
