<?php

namespace App\Repository;

use App\Entity\PlaylistEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PlaylistEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlaylistEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlaylistEntry[]    findAll()
 * @method PlaylistEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlaylistEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlaylistEntry::class);
    }

    // /**
    //  * @return PlaylistEntry[] Returns an array of PlaylistEntry objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PlaylistEntry
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
