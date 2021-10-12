<?php

namespace App\Controller;

use App\Document\Song;
use App\Service\Recommender;
use ONGR\ElasticsearchBundle\Service\IndexService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class RecommendationController extends AbstractController
{
    /**
     * @Route("/recommendation/listen/{songId}", name="recommendation_listen", requirements={"songId": "\d+"}, methods={"POST"})
     */
    public function listen(int $songId, Recommender $recommender)
    {
        $recommender->track($songId, $this->getUser()->getId());
        // TODO this does not work due to playcount not being imported
        //$this->getSongManager()->update($songId, [], 'ctx._source.play_count+=1');

        return $this->json('OK');
    }

    private function getSongManager(): IndexService
    {
        return $this->container->get(Song::class);
    }
}
