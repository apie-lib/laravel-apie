<?php

namespace Apie\LaravelApie\Wrappers\Cache;

use Illuminate\Contracts\Cache\Repository as Cache;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class LaravelCache implements CacheInterface
{
    public function __construct(
        private readonly Cache $cache,
    ) {
    }

    /**
     * @param array<array-key, mixed>|null $metadata
     */
    public function get(
        string $key,
        callable $callback,
        ?float $beta = null,
        ?array &$metadata = null,
    ): mixed {
        return $this->cache->remember(
            $key,
            now()->addMinutes(60),
            fn () => $callback($this->createItem($key), true),
        );
    }

    public function delete(string $key): bool
    {
        return $this->cache->forget($key);
    }

    private function createItem(string $key): ItemInterface
    {
        return new LaravelCacheItem($key);
    }
}
