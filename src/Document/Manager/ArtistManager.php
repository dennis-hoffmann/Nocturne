<?php

namespace App\Document\Manager;

use App\Document\Song;
use ONGR\ElasticsearchBundle\Result\Aggregation\AggregationValue;
use ONGR\ElasticsearchBundle\Service\IndexService;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\StatsAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\TopHitsAggregation;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Sort\FieldSort;
use Psr\Container\ContainerInterface;

class ArtistManager
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getArtistSongIds(int $artistId): array
    {
        $songIds = [];

        $search = $this->getSongManager()->createSearch();
        $search->setSource(false);
        $search->addQuery(new TermQuery('artistid', $artistId));
        $search->setScroll('1m');

        $docs = $this->getSongManager()->findRaw($search);

        do {
            $songIds[] = (int)$docs->current()['_id'];
        } while ($docs->next() && $docs->valid());

        return $songIds;
    }

    /**
     * @param int $id
     * @return array Songs and albums
     */
    public function getArtistById(int $id): array
    {
        $search = $this->getSongManager()->createSearch();
        $search->addUriParam('_source', SongManager::DEFAULT_SOURCE);
        $search->setSize(1000);

        $termQuery = new TermQuery('artistid', $id);
        $search->addQuery($termQuery);

        $termsAgg = new TermsAggregation('album_agg', 'album.keyword');

        // Sort by Year
        $termsAgg->addAggregation(new StatsAggregation('year', 'year'));
        $termsAgg->setParameters([
            'size' => 50,
            'order' => ['year.max' => 'desc'],
        ]);

        $topHitsAgg = new TopHitsAggregation('title_agg', 1);
        $topHitsAgg->addParameter('_source', ['album_id', 'artistname', 'artistid', 'thumbnail', 'year']);
        $termsAgg->addAggregation($topHitsAgg);

        $search->addAggregation($termsAgg);

        $search->addSort(new FieldSort('year', FieldSort::DESC));
        $search->addSort(new FieldSort('album_id', FieldSort::ASC));
        $search->addSort(new FieldSort('track', FieldSort::ASC));

        $result = $this->getSongManager()->findDocuments($search);

        $albums = [];

        foreach ($result->getAggregation('album_agg')['buckets'] as $bucket) {
            $additionalInfo = $bucket['title_agg']['hits']['hits'][0]['_source'];

            $albums[] = [
                'title' => $bucket['key'],
                'id' => $additionalInfo['album_id'],
                'thumbnail' => !empty($additionalInfo['thumbnail']) ? $additionalInfo['thumbnail'] : null,
                'year' => $additionalInfo['year'],
                'artist' => $additionalInfo['artistname'],
                'artistid' => $additionalInfo['artistid'],
                'songs' => $bucket[AggregationValue::DOC_COUNT_KEY],
            ];
        }

        return array_merge(
            ($result->count() ? ($result->getRaw()['hits']['hits'][0]['_source']['artist'] ?? []) : []),
            [
                'songs' => $result,
                'albums' => $albums,
            ]
        );
    }

    public function getArtists(array $source = null): array
    {
        $search = $this->getSongManager()->createSearch();
        $search->addSort(new FieldSort('artistname.keyword', FieldSort::ASC));
        $search->setSize(300);
        $search->setSource(false);

        $topHitsAgg = new TopHitsAggregation('title_agg', 1);
        if (!empty($source)) {
            $topHitsAgg->addParameter('_source', $source);
        }

        $termsAgg = new TermsAggregation('artist_agg', 'artistname.keyword');
        $termsAgg->addAggregation($topHitsAgg);
        $termsAgg->setParameters([
            'size' => 300,
            'order' => ['_key' => FieldSort::ASC],
        ]);
        $search->addAggregation($termsAgg);

        $result = $this->getSongManager()->findRaw($search);

        $artists = [];
        foreach ($result->getAggregation('artist_agg')['buckets'] as $bucket) {
            $additionalInfo = $bucket['title_agg']['hits']['hits'][0]['_source'];
            $artists[] = $additionalInfo['artist'];
        }

        return $artists;
    }

    private function getSongManager(): IndexService
    {
        return $this->container->get(Song::class);
    }
}