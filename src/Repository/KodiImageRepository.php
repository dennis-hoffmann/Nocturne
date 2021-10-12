<?php

namespace App\Repository;

use App\Entity\KodiImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method KodiImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method KodiImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method KodiImage[]    findAll()
 * @method KodiImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KodiImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KodiImage::class);
    }

    // /**
    //  * @return KodiImage[] Returns an array of KodiImage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('k.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function findOneByTargetIdAndType(int $id, string $type, ?int $version = null): ?KodiImage
    {
        return $this->createQueryBuilder('k')
            ->join('k.imageType', 't')
            ->andWhere('k.targetId = :id')
            ->andWhere('t.name = :name')
            ->andWhere('k.version = :version')
            ->setParameter('id', $id)
            ->setParameter('name', $type)
            ->setParameter('version', $version ?: 1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
