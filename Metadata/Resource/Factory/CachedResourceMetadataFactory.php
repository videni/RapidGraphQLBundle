<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Metadata\Resource\Factory;

use App\Bundle\RestBundle\Cache\CachedTrait;
use App\Bundle\RestBundle\Metadata\Resource\ResourceMetadata;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Caches resource metadata.
 */
final class CachedResourceMetadataFactory implements ResourceMetadataFactoryInterface
{
    use CachedTrait;

    const CACHE_KEY_PREFIX = 'resource_metadata_';

    private $decorated;

    public function __construct(CacheItemPoolInterface $cacheItemPool, ResourceMetadataFactoryInterface $decorated)
    {
        $this->cacheItemPool = $cacheItemPool;
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $resourceClass): ResourceMetadata
    {
        $cacheKey = self::CACHE_KEY_PREFIX.md5($resourceClass);

        return $this->getCached($cacheKey, function () use ($resourceClass) {
            return $this->decorated->create($resourceClass);
        });
    }

    public function getAllResourceMetadatas(): array
    {
        return $this->decorated->getAllResourceMetadatas();
    }
}
