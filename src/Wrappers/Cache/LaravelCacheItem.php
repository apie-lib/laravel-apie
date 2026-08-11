<?php

namespace Apie\LaravelApie\Wrappers\Cache;

use Apie\Core\ValueObjects\Utils;
use Symfony\Contracts\Cache\ItemInterface;

class LaravelCacheItem implements ItemInterface
{
    private ?int $expiration = null;

    /**
     * @var array<array-key, mixed> $metadata
     */
    private array $metadata = [];

    public function __construct(
        private readonly string $key,
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        return null;
    }

    public function isHit(): bool
    {
        return false;
    }

    public function set(mixed $value): static
    {
        return $this;
    }

    public function expiresAt(?\DateTimeInterface $expiration): static
    {
        $this->expiration = $expiration?->getTimestamp();

        return $this;
    }

    public function expiresAfter(int|\DateInterval|null $time): static
    {
        if ($time instanceof \DateInterval) {
            $now = new \DateTimeImmutable();
            $this->expiration = $now->add($time)->getTimestamp();
        } elseif ($time !== null) {
            $this->expiration = time() + $time;
        } else {
            $this->expiration = null;
        }

        return $this;
    }

    public function tag($tags): static
    {
        $this->metadata['tags'] = is_string($tags) ? [$tags] : Utils::toArray($tags);

        return $this;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata + ['expiration' => $this->expiration];
    }
}
