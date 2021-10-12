<?php

namespace App\Repository;

use App\Entity\AudioWaveform;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AudioWaveform|null find($id, $lockMode = null, $lockVersion = null)
 * @method AudioWaveform|null findOneBy(array $criteria, array $orderBy = null)
 * @method AudioWaveform[]    findAll()
 * @method AudioWaveform[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AudioWaveformRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AudioWaveform::class);
    }

    public function findOneBySourceId(string $sourceId): ?AudioWaveform
    {
        return $this
            ->createQueryBuilder('a')
            ->andWhere('a.sourceId = :sourceId')
            ->setParameter('sourceId', $sourceId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function waveformExists(string $sourceId): bool
    {
        $stmt = $this->getEntityManager()->getConnection()->prepare('
            SELECT 1 
        
            FROM audio_waveform aw

            WHERE aw.source_id = ?
            
            LIMIT 1
        ');

        $stmt->execute([$sourceId]);

        return (bool) $stmt->rowCount();
    }
}
