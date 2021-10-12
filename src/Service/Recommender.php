<?php

namespace App\Service;

use App\Document\Manager\ArtistManager;
use App\Document\Playback;
use App\Document\Song;
use ONGR\ElasticsearchBundle\Result\Aggregation\AggregationValue;
use ONGR\ElasticsearchBundle\Service\IndexService;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\TopHitsAggregation;
use ONGR\ElasticsearchDSL\Query\Specialized\MoreLikeThisQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermsQuery;
use Psr\Container\ContainerInterface;

class Recommender
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ArtistManager
     */
    private $artistManager;

    public function __construct(ContainerInterface $container, ArtistManager $artistManager)
    {
        $this->container = $container;
        $this->artistManager = $artistManager;
    }

    public function track(int $id, int $userId)
    {
        $playback = new Playback();
        $playback
            ->setCreated(new \DateTime())
            ->setSongId($id)
            ->setUserId($userId);

        $this->getPlaybackManager()->persist($playback);
        $this->getPlaybackManager()->flush();
    }

    public function recommend($userId): array
    {
        $search = $this->getSongManager()->createSearch();
        $ids = $this->getUserPlaybacks($userId, 500);

        $likeThisQuery = new MoreLikeThisQuery(null, [
            'max_query_terms' => 500,
            'min_doc_freq' => 50,
            'include' => true,
            'fields' => ['title', 'album.keyword', 'artistname.keyword', 'genre'],
            'like' => array_map(function ($id) {
                return [
                    '_index' => $this->getSongManager()->getIndexName(),
                    '_id' => $id,
                ];
            }, $ids)
        ]);

        // Aggregate Albums
        $termsAgg = new TermsAggregation('album_agg', 'album.keyword');
        $topHitsAgg = new TopHitsAggregation('title_agg', 1);
        $termsAgg->addAggregation($topHitsAgg);
        $termsAgg->setParameters([
            'size' => 25,
        ]);
        $search->addAggregation($termsAgg);

        $search->addQuery($likeThisQuery);
        $search->setSize(25);
        $result = $this->getSongManager()->findDocuments($search);

        $albums = [];
        $songs = [];
        foreach ($result->getAggregation('album_agg')->getBuckets() as $bucket) {
            foreach ($bucket->getValue('title_agg')['hits']['hits'] as $song) {
                $songs[] = array_merge(
                    $song['_source'],
                    [
                        'id' => $song['_id'],
                        '_score' => $song['_score']
                    ]
                );
            }

            $album = $song['_source'];

            $albums[] = [
                'title' => $bucket->getValue('key'),
                'id' => $album['album_id'],
                'thumbnail' => !empty($album['thumbnail']) ? $album['thumbnail'] : null,
                'year' => $album['year'],
                'artist' => $album['artistname'],
                'artistid' => $album['artistid'],
                'songs' => $bucket->getValue(AggregationValue::DOC_COUNT_KEY),
                '_score' => $song['_score'],
            ];
        }

        usort($songs, [$this, 'sortByScore']);
        usort($albums, [$this, 'sortByScore']);

        return [
            'songs' => $songs,
            'albums' => $albums,
        ];
    }

    public function getRecentlyPlayedSongs(int $userId, \DateTime $since = null, \DateTime $until = null)
    {
        $search = $this->getPlaybackManager()->createSearch();

        $since = $since ?? new \DateTime('-7 days');
        $until = $until ?? new \DateTime();

        $userQuery = new TermQuery('user_id', $userId);
        $rangeQuery = new RangeQuery('created', [
            'gte' => $since->format('Y-m-d H:i:s'),
            'lte' => $until->format('Y-m-d H:i:s'),
            'format' => 'yyyy-MM-dd HH:mm:ss'
        ]);

        $playsAgg = new TermsAggregation('plays_agg', 'song_id');
        $playsAgg->setParameters([
            'size' => 50,
        ]);

        $search
            ->addQuery($rangeQuery)
            ->addQuery($userQuery)
            ->addAggregation($playsAgg)
        ;

        $result = $this->getPlaybackManager()->findDocuments($search);

        $playbacks = [];
        foreach ($result->getAggregation('plays_agg')->getBuckets() as $bucket) {
            $playbacks[$bucket['key']] = $bucket[AggregationValue::DOC_COUNT_KEY];
        }

        return $playbacks;
    }

    public function getArtistTopSongs(int $artistId, int $userId = null, int $count = 10): array
    {
        $songIds = $this->artistManager->getArtistSongIds($artistId);

        $search = $this->getPlaybackManager()->createSearch();
        $search->addQuery(new TermsQuery('song_id', $songIds));
        $search->setSource(false);

        if ($userId) {
            $search->addQuery(new TermQuery('user_id', $userId));
        }

        $termsAgg = new TermsAggregation('song_agg', 'song_id');
        $termsAgg->setParameters([
            'size' => $count,
        ]);

        $search->addAggregation($termsAgg);

        $res = $this->getPlaybackManager()->findDocuments($search);

        return array_map(function (AggregationValue $bucket) {
            return $bucket->getValue('key');
        }, $res->getAggregation('song_agg')->getBuckets());
    }

    public function getUserPlaybacks(int $userId, int $limit = 100): array
    {
        $docs = $this->getPlaybackManager()->findBy([
            'user_id' => $userId,
        ], [], $limit);

        $ids = [];
        while ($docs->valid()) {
            /** @var Playback $doc */
            $doc = $docs->current();
            $ids[] = $doc->getSongId();
            $docs->next();
        }

        return $ids;
    }

    private function sortByScore($a, $b)
    {
        return $a['_score'] < $b['_score'];
    }


    private function getPlaybackManager(): IndexService
    {
        return $this->container->get(Playback::class);
    }

    private function getSongManager(): IndexService
    {
        return $this->container->get(Song::class);
    }
}