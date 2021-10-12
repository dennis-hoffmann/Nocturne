<?php

namespace App\Controller;

use App\Document\Manager\SongManager;
use App\Elastic\RomajiAnalyzer;
use App\Service\Music163LyricsFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LyricsController extends AbstractController
{
    /**
     * @Route("/lyrics/{songId}", name="lyrics_get", requirements={"songId"="\d+"})
     */
    public function fetch(int $songId, SongManager $songManager, Music163LyricsFetcher $lyricsFetcher, RomajiAnalyzer $romajiAnalyzer)
    {
        $song = $songManager->getSongById($songId);

        if ($song->getLyrics()) {
            return $this->json([
                'lyrics' => $song->getLyrics(),
                'rawLyrics' => $song->getRawLyrics(),
            ]);
        }

        $candidates = $lyricsFetcher->findSongCandidates($song);

        if (empty($candidates)) {
            return $this->json('No lyrics found.', 404);
        }

        // Todo let user set his candidate in frontend
        $chosen = $candidates[0];

        $lyrics = $lyricsFetcher->findSongLyrics($chosen['id']);

        if (!$lyrics) {
            return $this->json('No lyrics found.', 404);
        }

        // Todo use timing in frontend. For now just strip them.
        $filtered = preg_replace('/\[.+?\]/', '', $lyrics);
        $filtered = str_replace('作词', 'Text', $filtered);
        $filtered = str_replace('作曲', 'Composition', $filtered);
        $filtered = str_replace("\n", '<br>', $filtered);
        $filtered = str_replace("　", '', $filtered);
        $filtered = str_replace("！", '!', $filtered);

        // Todo somehow identify if lyrics are japanese :thinking:
        $romaji = $romajiAnalyzer->analyze($filtered);

        // Update Song in Elastic
        $songManager->getSongManager()->update($song->getId(), [
            'lyrics' => $romaji,
            'raw_lyrics' => $lyrics,
        ]);

        return $this->json([
            'lyrics' => $romaji,
            'rawLyrics' => $lyrics,
        ]);
    }
}