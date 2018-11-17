<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Routing\PathResolver;

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
     * @param array       $operation         The operation metadata
     * @param string|bool $operationType     One of the constants defined in ApiPlatform\Core\Api\OperationType
     *                                       If the property is a boolean, true represents OperationType::COLLECTION, false is for OperationType::ITEM
     */
    public function resolveOperationPath(string $resourceShortName, array $operation, $operationType/*, string $operationName = null*/): string;
}
