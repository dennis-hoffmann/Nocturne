<?php

namespace App\Mercure;

use App\Service\Mercure;
use Clue\React\EventSource\EventSource;
use React\EventLoop\LoopInterface;

class SubscriberFactory
{
    /**
     * @var Mercure
     */
    private $mercure;

    public function __construct(Mercure $mercure)
    {
        $this->mercure = $mercure;
    }

    public function create(array $topics, LoopInterface $loop, string $subscriptionCollectionTopic = null)
    {
        $lastEventId = null;
        $jwt = $this->mercure->getJwtForTopics($topics);

        if ($subscriptionCollectionTopic) {
            $subscriptionCollection = $this->mercure->getSubscriptionCollection($jwt, $subscriptionCollectionTopic);

            $lastEventId = $subscriptionCollection['lastEventID'] ?? null;

            if ($lastEventId) {
                $topics[] = $this->mercure->buildSubscribeEventsTopic($subscriptionCollectionTopic);
            }
        }

        $url = $this->mercure->buildSubscribeUrl($topics, $lastEventId);

        return new EventSource($url, $loop, null, [
//            'Authorization' => sprintf('Bearer: %s', $jwt),
            'Cookie' => 'mercureAuthorization=' . $jwt,
        ]);
    }
}
