<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Routing\PathResolver;

use App\Bundle\RestBundle\Operation\OperationType;
use App\Bundle\RestBundle\Exception\InvalidArgumentException;
use App\Bundle\RestBundle\Operation\PathSegmentNameGeneratorInterface;
use App\Bundle\RestBundle\Operation\ActionTypes;

/**
 * Generates an operation path.
 */
final class OperationPathResolver implements OperationPathResolverInterface
{
    private $pathSegmentNameGenerator;

    public function __construct(PathSegmentNameGeneratorInterface $pathSegmentNameGenerator)
    {
        $this->pathSegmentNameGenerator = $pathSegmentNameGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveOperationPath(string $resourceShortName, array $operation, string $operationName = null): string
    {
        if (isset($operation['path'])) {
            return $operation['path'];
        }

        $path = '/'.$this->pathSegmentNameGenerator->getSegmentName($resourceShortName, true);
        if (ActionTypes::isSingleItemAction($operation['action'])) {
            $path .= '/{id}';
        }

        return $path;
    }
}
