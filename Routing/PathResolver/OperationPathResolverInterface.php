<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Routing\PathResolver;

use App\Bundle\RestBundle\Config\Resource\OperationConfig;

/**
 * Resolves the path of a resource operation.
 *
 */
interface OperationPathResolverInterface
{
    /**
     * Resolves the operation path.
     *
     * @param string      $resourceShortName When the operation type is a subresource and the operation has more than one identifier, this value is the previous operation path
     * @param OperationConfig       $operation         The operation config
     */
    public function resolveOperationPath(string $resourceShortName, OperationConfig $operation, string $operationName): string;
}
