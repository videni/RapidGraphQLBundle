<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Routing\PathResolver;

use Videni\Bundle\RestBundle\Config\Resource\Operation;

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
     * @param Operation       $operation         The operation config
     */
    public function resolveOperationPath(string $resourceShortName, Operation $operation, string $operationName): string;
}
