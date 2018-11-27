<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Provider;

use App\Bundle\RestBundle\Config\Resource\ResourceConfig;

/**
 * Extracts formats for a given operation according to the retrieved Metadata.
 */
interface FormatsProviderInterface
{
    /**
     * Finds formats for an operation.
     */
    public function getFormats(ResourceConfig $resourceConfig, string $operationName): array;
}
