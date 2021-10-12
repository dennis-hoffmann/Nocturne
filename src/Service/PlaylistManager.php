<?php

namespace App\Service;

use App\Entity\Playlist;
use App\Document\Playlist as ElasticPlaylist;
use App\Entity\PlaylistEntry;
use App\Entity\User;
use App\Exception\PlaylistAlreadyExistsException;
use App\Repository\PlaylistRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use ONGR\ElasticsearchBundle\Service\IndexService;
use Psr\Container\ContainerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PlaylistManager
{
    /**
     * @var PlaylistRepository
     */
    private $repo;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
        PlaylistRepository $playlistRepository,
        EntityManagerInterface $em,
        ContainerInterface $container
    )
    {
        $this->repo = $playlistRepository;
        $this->em = $em;
        $this->container = $container;
    }

    public function getPlaylist(int $id): ?Playlist
    {
        return $this->repo->find($id);
    }

    public function getPlaylists()
    {
        return $this->repo->findAll();
    }

    public function create(string $name, ?UserInterface $user): Playlist
    {
        if ($this->repo->findOneByNameAndUser($name, $user)) {
            throw new PlaylistAlreadyExistsException();
        }

        $playlist = new Playlist();

        $playlist
            ->setName($name)
            ->setOwner($user)
        ;

        $this->em->persist($playlist);
        $this->em->flush($playlist);

        return $playlist;
    }

    /**
     * @param Playlist $playlist
     * @param array $entries Either an array of SongIds or an array of arrays like ['songId' => 123, 'position' => 10]
     *
     * @return Playlist
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addEntries(Playlist $playlist, array $entries)
    {
        foreach ($entries as $key => $candidate) {
            $key++;

            if (!is_array($candidate)) {
                $candidate = ['songId' => $candidate];
            }

            $position = $candidate['position'] ?? $this->getMaxPosition($playlist) + $key;

            if ($this->playlistHasSong($playlist, $candidate['songId'])) {
                continue;
            }

            $entry = new PlaylistEntry();
            $entry
                ->setSongId($candidate['songId'])
                ->setAdded(new \DateTime())
                ->setPosition($candidate['position'] ?? $position)
                ->setPlaylist($playlist)
            ;

            $this->em->persist($entry);
        }

        $this->em->flush();

        // Fetch playlist again to get new entries.
        return $this->getPlaylist($playlist->getId());
    }

    public function removeEntries(Playlist $playlist, array $songIds)
    {
        $stmt = $this->em->getConnection()->prepare("
            DELETE FROM playlist_entry pe 
            
            WHERE 
                pe.playlist_id = :playlist_id
                AND pe.song_id IN (:song_ids)       
        ");

        $stmt->execute([
            'playlist_id' => $playlist->getId(),
            'song_ids' => implode(',', $songIds)
        ]);

        // Fetch playlist again to get new entries.
        return $this->getPlaylist($playlist->getId());
    }

    /**
     * @param User $user
     *
     * @return ElasticPlaylist[]
     */
    public function getElasticUserPlaylists(User $user)
    {
        return iterator_to_array($this->getElastic()->findBy(['owner_id' => $user->getId()], ['created' => 'DESC'], 100));
    }

    public function getElasticPlaylist(int $playlistId): ?ElasticPlaylist
    {
        try {
            return $this->getElastic()->find($playlistId);
        } catch (Missing404Exception $exception) {
            return null;
        }
    }

    private function getMaxPosition(Playlist $playlist) {
        $stmt = $this->em->getConnection()->prepare("
            SELECT MAX(pe.position) 
            
            FROM playlist_entry pe
            
            WHERE pe.playlist_id = :playlist_id
        ");

        $stmt->execute(['playlist_id' => $playlist->getId()]);

        if (!$stmt->rowCount()) {
            return -1;
        }

        return $stmt->fetch(\PDO::FETCH_COLUMN);
    }

    public function playlistHasSong(Playlist $playlist, $songId)
    {
        return $playlist->getEntries()->exists(function ($key, PlaylistEntry $entry) use ($songId) {
            return $entry->getSongId() === $songId;
        });
    }

    public function getElastic(): IndexService
    {
        return $this->container->get(ElasticPlaylist::class);
    }
}