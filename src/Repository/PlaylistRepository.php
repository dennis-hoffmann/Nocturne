<?php

namespace App\Repository;

use App\Entity\Playlist;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Playlist|null find($id, $lockMode = null, $lockVersion = null)
 * @method Playlist|null findOneBy(array $criteria, array $orderBy = null)
 * @method Playlist[]    findAll()
 * @method Playlist[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlaylistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Playlist::class);
    }

    // /**
    //  * @return Playlist[] Returns an array of Playlist objects
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

    public function findOneByNameAndUser(string $name, ?User $user): ?Playlist
    {
        $qb = $this
            ->createQueryBuilder('p')
            ->andWhere('p.name = :name')
            ->setParameter('name', $name)
        ;

        if ($user) {
            $qb
                ->andWhere('p.owner = :user')
                ->setParameter('user', $user)
            ;
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
