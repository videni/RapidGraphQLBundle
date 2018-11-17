<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Metadata\Resource\Factory;

use App\Bundle\RestBundle\Exception\ResourceClassNotFoundException;
use App\Bundle\RestBundle\Metadata\Resource\ResourceMetadata;

/**
 * Creates a resource metadata value object.
 */
interface ResourceMetadataFactoryInterface
{
    /**
     * Creates a resource metadata.
     *
     *
     * @throws ResourceClassNotFoundException
     */
    public function create(string $resourceClass): ResourceMetadata;

    public function getAllResourceMetadatas(): array;
}
