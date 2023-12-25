<?php

namespace App\Repository;

use App\Entity\PlaylistCounter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlaylistCounter>
 *
 * @method PlaylistCounter|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlaylistCounter|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlaylistCounter[]    findAll()
 * @method PlaylistCounter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlaylistCounterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlaylistCounter::class);
    }

//    /**
//     * @return PlaylistCounter[] Returns an array of PlaylistCounter objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PlaylistCounter
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
