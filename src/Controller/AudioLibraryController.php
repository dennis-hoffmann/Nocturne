<?php

namespace App\Controller;

use App\Document\Manager\AlbumManager;
use App\Document\Manager\ArtistManager;
use App\Document\Manager\SongManager;
use App\Document\Song;
use App\Elastic\FilterManager;
use App\Exception\KodiImageAlreadyExistsException;
use App\Service\KodiImageImporter;
use App\Service\Recommender;
use App\Service\UserPlaylist;
use Doctrine\Common\Collections\ArrayCollection;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use ONGR\ElasticsearchBundle\Result\Aggregation\AggregationValue;
use ONGR\ElasticsearchBundle\Service\IndexService;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\StatsAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\TopHitsAggregation;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\FullText\MultiMatchQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\PrefixQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermsQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AudioLibraryController extends AbstractController
{
    /**
     * @Route("/library/audio/userplaylist", name="user_playlist")
     */
    public function userPlaylist(Request $request, UserPlaylist $userPlaylist, SongManager $songManager): Response
    {
        switch ($request->getMethod()) {
            case Request::METHOD_POST:
                return $this->json($userPlaylist->set($this->getUser(), json_decode($request->getContent(), true)));
            case Request::METHOD_GET:
                return $this->json($songManager->getSongsByIds($userPlaylist->get($this->getUser()), true, array_merge(SongManager::DEFAULT_SOURCE, ['waveform'])));
            default:
                return $this->json('Invalid Request', 400);
        }
    }

    /**
     * @Route("/library/audio/song/{id}", name="library_audio_song_get", requirements={"id"="\d+"}, methods={"GET"})
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function getSong(Request $request, int $id): Response
    {
        try {
            $song = $this->getSongManager()->find($id);
        } catch (Missing404Exception $e) {
            throw new NotFoundHttpException(sprintf('Song with ID %d not found.', $id));
        }

        return $this->json($song);
    }

    /**
     * @Route("/library/audio/song/{id}", name="library_audio_song_update", requirements={"id"="\d+"}, methods={"POST"})
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function updateSong(Request $request, int $id, SongManager $songManager): Response
    {
        try {
            $song = $songManager->getSongById($id);
        } catch (Missing404Exception $e) {
            throw new NotFoundHttpException(sprintf('Song with ID %d not found.', $id));
        }

        $newSongData = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException(sprintf('Invalid JSON provided: "%s"', json_last_error_msg()));
        }

        if (empty($newSongData)) {
            throw new \InvalidArgumentException('Empty data provided');
        }

        $allowedData = array_filter([
            'title' => $newSongData['title'] ?? null,
            'lyrics' => $newSongData['lyrics'] ?? null,
            'track' => $newSongData['track'] ?? null,
        ]);

        if (!empty($allowedData)) {
            $songManager->getSongManager()->update($song->getId(), $allowedData);
        }

        return $this->json('OK');
    }

    /**
     * @Route("/library/audio/album/{id}", name="library_audio_album_get", requirements={"id"="\d+"}, methods={"GET"})
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function getAlbum(Request $request, int $id, SongManager $songManager): JsonResponse
    {
        try {
            $songs = $songManager->getSongsByAlbumId($id, array_merge(SongManager::DEFAULT_SOURCE, ['lyrics', 'waveform']));
        } catch (Missing404Exception $e) {
            throw new NotFoundHttpException(sprintf('Album with ID %d not found.', $id));
        }

        /** @var Song $firstSong */
        $firstSong = $songs[0];

        return $this->json([
            'songs' => $songs,
            'title' => $firstSong->getAlbum(),
            'id' => $firstSong->getAlbumId(),
            'thumbnail' => $firstSong->getThumbnail(),
            'year' => $firstSong->getYear(),
            'artist' => $firstSong->getArtist()->getName(),
            'artistId' => $firstSong->getArtist()->getId(),
            'genre' => $firstSong->getGenre(),
            // Todo
            'description' => '',
            'songCount' => count($songs),
        ]);
    }

    /**
     * @Route("/library/audio/album/{id}", name="library_audio_album_update", requirements={"id"="\d+"}, methods={"POST"})
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function updateAlbum(
        Request $request,
        int $id,
        AlbumManager $albumManager,
        SongManager $songManager,
        KodiImageImporter $imageImporter,
        string $uploadBaseDir
    ): JsonResponse
    {
        $updatedFields = [];

        try {
            $album = $albumManager->getAlbumById($id);
        } catch (Missing404Exception $e) {
            throw new NotFoundHttpException(sprintf('Album with ID %d not found.', $id));
        }

        if (strpos($request->headers->get('Content-Type', 'application/json'), 'multipart/form-data') !== false) {
            if (!$request->files->count()) {
                return $this->json('No files in FormData request provided.', 400);
            }

            $allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];
            $imageType = $imageImporter->getImageTypeByName(KodiImageImporter::IMAGE_TYPE_ALBUM);

            /** @var UploadedFile $file */
            foreach ($request->files->all() as $file) {
                $mime = $file->getMimeType();
                if (!in_array($mime, $allowedMimeTypes)) {
                    return $this->json(sprintf('Expected mime type to be one of %s. Received %s', implode(', ', $allowedMimeTypes), $mime), 400);
                }

                $movedFile = $file->move($uploadBaseDir . '/album', $file->getClientOriginalName());

                $doImportImage = function (int $version = 1) use ($movedFile, $album, $imageType, $imageImporter) {
                    $image = $imageImporter->createImageFromUrl($movedFile->getPathname(), $album['title'], $album['id'], $imageType, $imageImporter->getBasePath() . $imageType->getPath(), $version);
                    $imageImporter->flushImages();

                    return $image;
                };

                try {
                    $image = $doImportImage();
                } catch (KodiImageAlreadyExistsException $e) {
                    $imageImporter->deleteImage($e->getImage());
                    $image = $doImportImage($e->getImage()->getVersion()+1);
                }

                $updatedFields['thumbnail'] = '/images' . $image->getImageType()->getPath() . $image->getFilename();

                // Only work with first file in case there somehow are multiple.
                break;
            }
        }

        if (!empty($updatedFields)) {
            foreach ($albumManager->getSongIdsByAlbumId($id) as $songId) {
                $songManager->getSongManager()->update($songId, $updatedFields);
            }
        }

        return $this->json('OK');
    }

    /**
     * @Route("/library/audio/artist/{id}", name="library_audio_artist", requirements={"id"="\d+"})
     *
     * @param Request $request
     * @param int $id
     * @param ArtistManager $artistManager
     * @param Recommender $recommender
     * @param SongManager $songManager
     *
     * @return JsonResponse
     */
    public function artist(Request $request, int $id, ArtistManager $artistManager, Recommender $recommender, SongManager $songManager): JsonResponse
    {
        $topSongs = $recommender->getArtistTopSongs($id);

        if ($topSongs) {
            $topSongs = $songManager->getSongsByIds($topSongs, true, array_merge(SongManager::DEFAULT_SOURCE, ['lyrics']));
        }

        return $this->json(array_merge($artistManager->getArtistById($id), ['topSongs' => $topSongs]));
    }

    /**
     * @Route("/library/audio/artists", name="library_audio_artists")
     */
    public function artists(ArtistManager $manager)
    {
        return new JsonResponse($manager->getArtists(['artist']));
    }

    /**
     * @Route("/library/audio/albums", name="library_audio_albums")
     */
    public function albums(AlbumManager $manager)
    {
        return new JsonResponse($manager->getAlbums());
    }

    /**
     * @Route("/library/audios", name="library_audio_songs", methods={"GET"}, format="json")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function songs(Request $request, SongManager $manager)
    {
        $ids = explode(',', $request->query->get('songs'));
        $includeWaveForm = $request->query->get('includeWaveForm', false);
        $source = SongManager::DEFAULT_SOURCE;

        if (empty($ids)) {
            throw new NotFoundHttpException('Missing ids.');
        }

        if ($includeWaveForm) {
            $source[] = 'waveform';
        }

        return $this->json($manager->getSongsByIds($ids, true, $source));
    }

    /**
     * @Route("/library/audio/filters", name="library_audio_filters", methods={"GET"}, format="json")
     */
    public function filters(FilterManager $filterManager)
    {
        return $this->json($filterManager->getFilters());
    }

    /**
     * @Route("/library/audio/random", name="library_audio_random", methods={"POST"}, format="json")
     *
     * @param Request $request
     * @param SongManager $manager
     *
     * @return JsonResponse
     */
    public function random(Request $request, SongManager $manager)
    {
        $filters = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json('Invalid filter data provided', 400);
        }

        $size = $request->query->get('size', 50);
        $negate = array_filter($filters['negations'] ?? []) ?? [];
        unset($filters['negations']);
        $filters = new ArrayCollection(array_filter($filters));

        if (empty($filters)) {
            $songs = $manager->getRandomSongs($size);
        } else {
            $bq = new BoolQuery();

            if ($filters->containsKey('artists')) {
                $tq = new TermsQuery('artistid', $filters->get('artists'));
                $bq->add($tq, !empty($negate['artist']) ? BoolQuery::MUST_NOT : BoolQuery::MUST);
            }

            if ($filters->containsKey('genres')) {
                $tq = new TermsQuery('genre', $filters->get('genres'));
                $bq->add($tq, !empty($negate['genre']) ? BoolQuery::MUST_NOT : BoolQuery::MUST);
            }

            if ($filters->containsKey('years')) {
                $range = $filters->get('years');

                if (count($range) === 2) {
                    $rq = new RangeQuery('year', [RangeQuery::GTE => $range[0], RangeQuery::LTE => $range[1]]);
                    $bq->add($rq, !empty($negate['year']) ? BoolQuery::MUST_NOT : BoolQuery::MUST);
                }
            }

            $songs = $manager->getRandomSongs($size, $bq);
        }

        return $this->json($songs);
    }

    /**
     * @Route(
     *     "/library/audio/search/{query}",
     *     name="search_library_audio",
     *     format="json",
     *     requirements={"query"="[\w ]+"}
     * )
     *
     * @param string $query
     *
     * @return JsonResponse
     */
    public function search(string $query): JsonResponse
    {
        $search = $this
            ->getSongManager()
            ->createSearch()
        ;
        $boolQuery = new BoolQuery();

        $matchQuery = new TermQuery('title.keyword', $query, ['boost' => 50]);
        $boolQuery->add($matchQuery, BoolQuery::SHOULD);

        $prefixQuery = new PrefixQuery('title.keyword', $query);
        $boolQuery->add($prefixQuery, BoolQuery::SHOULD);

        $multiMatchQuery = new MultiMatchQuery(
            [
                'artistname',
                'album',
                'title',
            ],
            $query,
            [
                'fuzziness' => 'AUTO',
            ]
        );
        $boolQuery->add($multiMatchQuery, BoolQuery::SHOULD);

        $search->addQuery($boolQuery);
        //$search->addSort(new FieldSort('play_count', FieldSort::DESC));
        //$search->addSort(new FieldSort('album_id'));
        $search->setSize(300);

        // Retrieve albums ordered by playcount
        $termsAgg = new TermsAggregation('album_agg', 'album_id');
        $termsAgg->addAggregation(new TopHitsAggregation('title_agg', 1));
        $termsAgg->addAggregation(new StatsAggregation('playcount', 'play_count'));
        $termsAgg->setParameters([
            'size' => 20,
            'order' => ['playcount.max' => 'desc'],
        ]);

        $artistAgg = new TermsAggregation('artist_agg', 'artistname.keyword');
        $artistAgg->addAggregation(new TopHitsAggregation('title_agg', 1));

        $search->addAggregation($termsAgg);
        $search->addAggregation($artistAgg);

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

        $artists = [];
        foreach ($result->getAggregation('artist_agg')->getBuckets() as $bucket) {
            $additionalInfo = $bucket->getValue('title_agg')['hits']['hits'][0]['_source'];
            $artists[] = $additionalInfo['artist'];
        }

        return $this->json([
            'songs' => $result,
            'albums' => $albums,
            'artists' => $artists,
        ]);
    }

    /**
     * @Route("library/audio/new_songs", name="library_audio_new", methods={"GET"})
     *
     * @param SongManager $songManager
     */
    public function bruh(SongManager $songManager)
    {
        $songs = $songManager->getNewestSongs(5);

        return $this->json([
            'songs' => $songs
        ]);
    }

    /**
     * @Route("/library/audio/recent", name="library_audio_recent", methods={"GET"})
     *
     * @param Recommender $recommender
     * @param SongManager $songManager
     *
     * @return JsonResponse
     */
    public function recentlyPlayed(Recommender $recommender, SongManager $songManager)
    {
        if (!$this->getUser()) {
            return $this->json([]);
        }

        $playedSongs = $recommender->getRecentlyPlayedSongs($this->getUser()->getId(), new \DateTime('-88 days'));

        if (empty($playedSongs)) {
            return $this->json([]);
        }

        $songs = $songManager->getSongsByIds(array_keys($playedSongs));

        return $this->json($songs);
    }

    /**
     * @Route("/library/audio/recommendations", name="library_audio_recos", methods={"GET"})
     *
     * @param Recommender $recommender
     *
     * @return JsonResponse
     */
    public function recommendations(Recommender $recommender)
    {
        // Todo this sucks
        return $this->json([
            'songs' => [],
            'albums' => [],
        ]);

        if (!$this->getUser()) {
            return $this->json([
                'songs' => [],
                'albums' => [],
            ]);
        }

        return $this->json($recommender->recommend($this->getUser()->getId()));
    }

    private function getSongManager(): IndexService
    {
        return $this->container->get(Song::class);
    }
}