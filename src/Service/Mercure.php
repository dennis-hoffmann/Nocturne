<?php

namespace App\Service;

use App\Entity\User;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Symfony\Component\Security\Core\Security;

class Mercure
{
    private $topics = ['general', 'audio_update'];

    /**
     * @var string
     */
    private $mercureSecretKey;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var string
     */
    private $hubUrl;


    public function __construct(Security $security, string $mercureSecretKey, string $hubUrl)
    {
        $this->mercureSecretKey = $mercureSecretKey;
        $this->security = $security;
        $this->hubUrl = $hubUrl;
    }

    public function buildSubscribeUrl(array $topics, ?string $lastEventId = null): Uri
    {
        $uri = new Uri($this->hubUrl);

        $query = '';
        foreach ($topics as $key => $topic) {
            $separator = $key === 0 ? '' : '&';
            $query .= $separator . 'topic=' . rawurlencode($topic);
        }

        $query .= $lastEventId ? '&Last-Event-ID=' . $lastEventId : '';

        return $uri->withQuery($query);
    }

    public function getSubscriptionCollection(string $jwt, string $topic): array
    {
        return json_decode($this->createClient($jwt)->get($this->hubUrl . '/subscriptions/' . $topic)->getBody()->getContents(), true);
    }

    public function buildSubscribeEventsTopic(string $topic): string
    {
        return '/.well-known/mercure/subscriptions/' . $topic . '{/subscriber}';
    }

    public function getTopics(): array
    {
        $user = $this->security->getUser();

        if ($user instanceof User && !in_array($user->getUsername(), $this->topics)) {
            $this->topics[] = $user->getUsername();
        }

        return $this->topics;
    }

    public function getJwt(): string
    {
        return (new Builder())
            ->withClaim('mercure', [
                'subscribe' => array_merge($this->getTopics(), ['/.well-known/mercure/subscriptions/{topic}{/subscriber}']),
                'publish' => ['playback_update']
            ])
            ->getToken(new Sha256(), new Key($this->mercureSecretKey));
    }

    public function getJwtForTopics(array $topics): string
    {
        return (new Builder())
            ->withClaim('mercure', [
                'subscribe' => $topics,
            ])
            ->getToken(new Sha256(), new Key($this->mercureSecretKey));
    }

    public function createClient(string $jwt): Client
    {
        return new Client([
            RequestOptions::HEADERS => [
                'Cookie' => 'mercureAuthorization=' . $jwt,
            ]
        ]);
    }
}
