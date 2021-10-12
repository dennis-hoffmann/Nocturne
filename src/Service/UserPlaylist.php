<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class UserPlaylist
{
    /**
     * @var CacheInterface
     */
    private $redis;

    public function __construct(CacheInterface $redis)
    {
        $this->redis = $redis;
    }

    public function get(User $user)
    {
        $cacheKey = 'app__userplaylist__' . $user->getId();

        return json_decode($this->redis->get($cacheKey, function (ItemInterface $item) {
            return json_encode([]);
        }), true);
    }

    public function set(User $user, array $playlist)
    {
        $cacheKey = 'app__userplaylist__' . $user->getId();

        $this->redis->delete($cacheKey);
        return json_decode($this->redis->get($cacheKey, function (ItemInterface $item) use ($playlist) {
            return json_encode($playlist);
        }), true);

    }
}