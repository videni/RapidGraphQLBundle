<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Provider;

use Videni\Bundle\RestBundle\Config\Resource\Resource;

/**
 * Extracts formats for a given operation according to the retrieved Metadata.
 */
interface FormatsProviderInterface
{
    /**
     * Finds formats for an operation.
     */
    public function getFormats(Resource $resourceConfig, string $operationName): array;
}
