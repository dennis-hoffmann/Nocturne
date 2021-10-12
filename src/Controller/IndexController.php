<?php

namespace App\Controller;

use App\Elastic\FilterManager;
use App\Http\Kodi;
use App\Repository\PackageRepository;
use App\Service\PlaylistManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->redirectToRoute('app');
    }

    /**
     * @Route("/command", name="debug_command")
     */
    public function command(\Symfony\Component\HttpKernel\KernelInterface $kernel)
    {
        $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
        $application->setAutoExit(false);

        $input = new \Symfony\Component\Console\Input\ArrayInput([
            'command' => 'kodi:index:songs',
        ]);

        $output = new \Symfony\Component\Console\Output\NullOutput();
        $application->run($input, $output);
    }

    /**
     * @Route("/app{path}", name="app", requirements={"path"=".*"})
     */
    public function app(
        PlaylistManager $playlistManager,
        string $path = ''
    ) {
        $playlists = $playlistManager->getElasticUserPlaylists($this->getUser());

        return $this->render('index/index.html.twig', [
            'playlists' => $playlists,
        ]);
    }

    /**
     * @Route(
     *     "/source/scan/{mediaSource}",
     *     name="scan_media_source",
     *     format="json",
     *     requirements={"mediaSource"="[\w ]+"}
     * )
     *
     * @param string $mediaSource
     * @param Kodi   $kodi
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function scanLibrarySource(string $mediaSource, Kodi $kodi): JsonResponse
    {
        try {
            $kodi->scanVideoLibrary($kodi->getMediaSourcePath($mediaSource));
            $success = true;
        } catch (\Exception $e) {
            $success = false;
        }

        return $this->json(['success' => $success]);
    }
}
