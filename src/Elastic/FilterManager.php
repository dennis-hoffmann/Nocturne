<?php

namespace App\Elastic;

use App\Document\Manager\ArtistManager;
use App\Document\Manager\SongManager;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class FilterManager
{
    /**
     * @var CacheInterface
     */
    private $redis;

    /**
     * @var ArtistManager
     */
    private $artistManager;

    /**
     * @var SongManager
     */
    private $songManager;

    public function __construct(CacheInterface $redis, ArtistManager $artistManager, SongManager $songManager)
    {
        $this->redis = $redis;
        $this->artistManager = $artistManager;
        $this->songManager = $songManager;
    }

    public function getFilters(): array
    {
        return $this->redis->get('app__filters', function (ItemInterface $item) {
            $item->expiresAfter(60*60*2);

            $artists = [];
            foreach ($this->artistManager->getArtists(['artist']) as $artist) {
                $artists[$artist['id']] = $artist['name'];
            }

            $genres = $this->songManager->getGenres();
            $years = $this->songManager->getYears();

            return [
                'artists' => $artists,
                'genres' => $genres,
                'years' => $years,
            ];
        });
    }
}