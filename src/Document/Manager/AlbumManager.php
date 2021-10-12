<?php

namespace App\Document\Manager;

use App\Document\Song;
use ONGR\ElasticsearchBundle\Result\Aggregation\AggregationValue;
use ONGR\ElasticsearchBundle\Service\IndexService;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\TopHitsAggregation;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Sort\FieldSort;
use Psr\Container\ContainerInterface;

class AlbumManager
{
    const DEFAULT_SOURCE = [
        'album',
        'album_id',
        'thumbnail',
        'year',
        'artistname',
        'artistid',
    ];

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getAlbums(array $source = null): array
    {
        $search = $this->getSongManager()->createSearch();
        $search->addSort(new FieldSort('album.keyword', FieldSort::ASC));
        $search->setSize(1000);
        $search->setSource(false);

        $topHitsAgg = new TopHitsAggregation('title_agg', 1);
        $topHitsAgg->addParameter('_source', $source ?: self::DEFAULT_SOURCE);

        $termsAgg = new TermsAggregation('album_agg', 'album_id');
        $termsAgg->addAggregation($topHitsAgg);
        $termsAgg->setParameters([
            'size' => 300,
            'order' => ['_key' => FieldSort::ASC],
        ]);
        $search->addAggregation($termsAgg);

        $result = $this->getSongManager()->findDocuments($search);

        $albums = [];
        foreach ($result->getAggregation('album_agg')->getBuckets() as $bucket) {
            $additionalInfo = $bucket->getValue('title_agg')['hits']['hits'][0]['_source'];

            $albums[] = [
                'title' => $additionalInfo['album'],
                'id' => $additionalInfo['album_id'],
                'thumbnail' => !empty($additionalInfo['thumbnail']) ? $additionalInfo['thumbnail'] : null,
                'year' => $additionalInfo['year'],
                'artist' => $additionalInfo['artistname'],
                'artistid' => $additionalInfo['artistid'],
                'songs' => $bucket->getValue(AggregationValue::DOC_COUNT_KEY),
            ];
        }

        return $albums;
    }

    public function getAlbumById(int $id): array
    {
        $search = $this
            ->getSongManager()
            ->createSearch()
            ->addQuery(new TermQuery('album_id', $id))
            ->setSize(1)
        ;

        $result = $this->getSongManager()->findArray($search);

        if (!$result->count()) {
            throw new \RuntimeException(sprintf('Album by id %d not found'));
        }

        $song = $result->first();

        return [
            'id' => $song['album_id'],
            'title' => $song['album'],
            'artist' => $song['artist'],
            'artistid' => $song['artistid'],
            'genre' => $song['genre'],
            'year' => $song['year'],
            'thumbnail' => $song['thumbnail'],
        ];
    }

    public function getSongIdsByAlbumId(int $albumId): array
    {
        $search = $this
            ->getSongManager()
            ->createSearch()
            ->addQuery(new TermQuery('album_id', $albumId))
            ->addAggregation(new TermsAggregation('song_ids', '_id'))
            ->setSize(1)
            ->setSource(false)
        ;

        return array_column(
            ($this->getSongManager()->findArray($search)->getAggregation('song_ids')['buckets'] ?? []),
            'key'
        );
    }

    private function getSongManager(): IndexService
    {
        return $this->container->get(Song::class);
    }
}