<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Provider;

use App\Bundle\RestBundle\Metadata\Resource\ResourceMetadata;

/**
 * Extracts formats for a given operation according to the retrieved Metadata.
 */
interface FormatsProviderInterface
{
    /**
     * Finds formats for an operation.
     */
    public function getFormats(ResourceMetadata $metadata, string $operationName): array;
}
