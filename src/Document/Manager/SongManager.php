<?php

namespace App\Document\Manager;

use App\Document\Song;
use Elasticsearch\Client;
use ONGR\ElasticsearchBundle\Result\Aggregation\AggregationValue;
use ONGR\ElasticsearchBundle\Result\DocumentIterator;
use ONGR\ElasticsearchBundle\Service\IndexService;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\Compound\FunctionScoreQuery;
use ONGR\ElasticsearchDSL\Query\MatchAllQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\IdsQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Sort\FieldSort;
use Psr\Container\ContainerInterface;

class SongManager
{
    const DEFAULT_SOURCE = [
        '_id',
        'added',
        'album',
        'album_id',
        'artist',
        'artistname',
        'artistid',
        'file',
        'genre',
        'length',
        'playable_file',
        'title',
        'track',
        'thumbnail',
        'year',
    ];

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getElastic(): Client
    {
        return $this->getSongManager()->getClient();
    }

    public function getSongById(int $id): Song
    {
        return $this->getSongManager()->find($id);
    }

    /**
     * @param int[] $ids
     * @param bool $sort
     * @param array $source
     *
     * @return array
     */
    public function getSongsByIds(array $ids, $sort = true, $source = self::DEFAULT_SOURCE)
    {
        $search = $this->getSongManager()->createSearch();
        $search->addQuery(new IdsQuery($ids));
        $search->addUriParam('_source', $source);
        $search->setSize(500);

        $songs = iterator_to_array($this->getSongManager()->findDocuments($search));

        if ($sort) {
            // Since elastic wont order by the given list of ids we do it ourselves.
            usort($songs, function ($a, $b) use ($ids) {
                $posA = array_search($a->getId(), $ids);
                $posB = array_search($b->getId(), $ids);

                return $posA - $posB;
            });
        }

        return $songs;
    }

    /**
     * @param int $albumId
     * @param array $source
     *
     * @return Song[]
     */
    public function getSongsByAlbumId(int $albumId, array $source = self::DEFAULT_SOURCE): array
    {
        $search = $this->getSongManager()->createSearch();
        $search->addQuery(new TermQuery('album_id', $albumId));
        $search->addSort(new FieldSort('track', FieldSort::ASC));
        $search->addUriParam('_source', $source);
        $search->setSize(50);

        return iterator_to_array($this->getSongManager()->findDocuments($search));
    }

    public function getSongByTitleAlbumAndArtist(string $title, string $album, string $artist): DocumentIterator
    {
        $bq = new BoolQuery();
        $bq->add(new TermQuery('title.keyword', $title));
        $bq->add(new TermQuery('album.keyword', $album));
        $bq->add(new TermQuery('artistname.keyword', $artist));

        $search = $this->getSongManager()->createSearch();
        $search->addQuery($bq);

        return $this->getSongManager()->findDocuments($search);

    }

    public function getNewestSongs(int $size = 50)
    {
        $search = $this
            ->getSongManager()
            ->createSearch()
            ->addUriParam('_source', self::DEFAULT_SOURCE)
        ;

        $search->addQuery(new MatchAllQuery());
        $search->setSize($size);
        $search->addSort(new FieldSort('added', FieldSort::DESC));

        return $this->getSongManager()->findDocuments($search);
    }

    public function getRandomSongs(int $size = 500, ?BoolQuery $bq = null)
    {
        $q = new FunctionScoreQuery($bq ?? new MatchAllQuery());
        $q->addRandomFunction();

        $search = $this->getSongManager()->createSearch();
        $search->addQuery($q);
        $search->addUriParam('_source', self::DEFAULT_SOURCE);
        $search->setSize($size);

        return $this->getSongManager()->findDocuments($search);
    }

    public function getGenres(): array
    {
        $search = $this
            ->getSongManager()
            ->createSearch()
            ->addQuery(new MatchAllQuery())
            ->setSource(false)
            ->addAggregation((new TermsAggregation('genre_agg', 'genre'))->setParameters(['size' => 300]))
        ;

        $docs = $this->getSongManager()->findDocuments($search);
        $genres = array_map(function (AggregationValue $value) {
            return $value->getValue('key');
        }, $docs->getAggregation('genre_agg')->getBuckets());

        sort($genres);

        return $genres;
    }

    public function getYears(): array
    {
        $search = $this
            ->getSongManager()
            ->createSearch()
            ->addQuery(new MatchAllQuery())
            ->setSource(false)
            ->addAggregation((new TermsAggregation('year_agg', 'year'))->setParameters(['size' => 300]))
        ;

        $docs = $this->getSongManager()->findDocuments($search);
        $years = array_filter(array_map(function (AggregationValue $value) {
            return $value->getValue('key');
        }, $docs->getAggregation('year_agg')->getBuckets()));

        sort($years);

        return $years;
    }

    public function getSongManager(): IndexService
    {
        return $this->container->get(Song::class);
    }
}