<?php

namespace App\Controller;

use App\Http\Kodi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class KodiController extends AbstractController
{
    /**
     * @Route("/scan/audio", name="scan_audio_library", format="json")
     *
     * @param Kodi $kodi
     *
     * @return JsonResponse
     */
    public function scanAudioLibrary(Kodi $kodi): JsonResponse
    {
        $kodi->scanAudioLibrary();

        return $this->json('OK');
    }

    /**
     * @Route("/clean/audio", name="clean_audio_library", format="json")
     *
     * @param Kodi $kodi
     *
     * @return JsonResponse
     */
    public function cleanAudioLibrary(Kodi $kodi): JsonResponse
    {
        $kodi->cleanAudioLibrary();

        return $this->json('OK');
    }
}