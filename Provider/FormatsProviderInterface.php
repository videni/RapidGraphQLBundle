<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Provider;

use Videni\Bundle\RestBundle\Config\Resource\Operation;

/**
 * Extracts formats for a given operation according to the retrieved Metadata.
 */
interface FormatsProviderInterface
{
    /**
     * Finds formats for an operation.
     */
    public function getFormats(Operation $opeartionConfig, string $actionName, $operationName): array;
}
