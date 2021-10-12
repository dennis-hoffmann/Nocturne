<?php

namespace App\Controller;

use App\Document\Manager\SongManager;
use App\Document\Object\PlaylistEntryObject;
use App\Exception\PlaylistAlreadyExistsException;
use App\Message\PlaylistReindexNotification;
use App\Service\PlaylistManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

class PlaylistController extends AbstractController
{
    /**
     * @Route("/playlist", name="playlist_create", methods={"POST"})
     *
     * @param Request $request
     * @param PlaylistManager $manager
     * @param MessageBusInterface $bus
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function create(Request $request, PlaylistManager $manager, MessageBusInterface $bus): JsonResponse
    {
        try {
            $data = $this->getRequestBodyAttributes(['name'], $request);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        try {
            $playlist = $manager->create($data['name'], $this->getUser());
        } catch (PlaylistAlreadyExistsException $e) {
            return $this->json(['success' => false, 'message' => 'Playlist already exists'], 400);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        $bus->dispatch(new PlaylistReindexNotification($playlist->getId()));

        return $this->json(['success' => true, 'id' => $playlist->getId()]);
    }

    /**
     * @Route("/playlist/{playlistId}", name="playlist_get", methods={"GET"}, requirements={"id": "\d+"})
     *
     * @param int $playlistId
     * @param PlaylistManager $manager
     * @param SongManager $songManager
     *
     * @return JsonResponse
     */
    public function playlist(int $playlistId, PlaylistManager $manager, SongManager $songManager)
    {
        $playlist = $manager->getElasticPlaylist($playlistId);

        if ($playlist->getOwnerId() !== null && $playlist->getOwnerId() !== $this->getUser()->getId()) {
            return $this->json('401 Unauthorized', 401);
        }

        $ids = $playlist->getEntries()->map(function (PlaylistEntryObject $entry) {
            return $entry->getSongId();
        });

        return $this->json($songManager->getSongsByIds($ids->getValues()));
    }

    /**
     * @Route("/playlists", name="playlists_get", methods={"GET"})
     *
     * @param PlaylistManager $manager
     *
     * @return JsonResponse
     */
    public function playlists(PlaylistManager $manager, SongManager $songManager)
    {
        if (!$this->getUser()) {
            return $this->json('401 Unauthorized', 401);
        }

        // Todo Rework playlist indexing. Right now this makes no sense.
        $lists = $manager->getElasticUserPlaylists($this->getUser());

        foreach ($lists as $key => $list) {
            $songIds = $list->getEntries()->map(function (PlaylistEntryObject $entry) {
                return $entry->getSongId();
            })->toArray();

            $lists[$key]->setEntries($songManager->getSongsByIds($songIds));
        }

        return $this->json($lists);
    }

    /**
     * @Route("/playlist/{id}/entries", name="playlist_add_entries", methods={"POST"}, requirements={"id": "\d+"})
     *
     * @param int $id
     * @param Request $request
     * @param PlaylistManager $manager
     * @param MessageBusInterface $bus
     *
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addEntries(int $id, Request $request, PlaylistManager $manager, MessageBusInterface $bus)
    {
        try {
            $data = $this->getRequestBodyAttributes(['entries'], $request);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        $playlist = $manager->getPlaylist($id);
        $playlist = $manager->addEntries($playlist, $data['entries']);

        $bus->dispatch(new PlaylistReindexNotification($playlist->getId()));

        return $this->json(['success' => true]);
    }

    /**
     * @Route("/playlist/{id}/entries", name="playlist_remove_entries", methods={"DELETE"}, requirements={"id": "\d+"})
     *
     * @param int $id
     * @param Request $request
     * @param PlaylistManager $manager
     * @param MessageBusInterface $bus
     *
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removeEntries(int $id, Request $request, PlaylistManager $manager, MessageBusInterface $bus)
    {
        try {
            $data = $this->getRequestBodyAttributes(['entries'], $request);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        $playlist = $manager->getPlaylist($id);
        $playlist = $manager->removeEntries($playlist, $data['entries']);

        $bus->dispatch(new PlaylistReindexNotification($playlist->getId()));

        return $this->json(['success' => true]);
    }

    private function getRequestBodyAttributes(array $attributes, Request $request): array
    {
        $body = json_decode($request->getContent(), true);

        foreach ($attributes as $requiredParam) {
            if (!isset($body[$requiredParam])) {
                $missingParams[] = $requiredParam;
            }
        }

        if (!empty($missingParams)) {
            throw new \Exception(sprintf('Missing required param(s) "%s".', implode('", "', $missingParams)));
        }

        return $body;
    }
}