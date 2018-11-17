<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Routing\PathResolver;

use App\Bundle\RestBundle\Operation\OperationType;
use App\Bundle\RestBundle\Exception\InvalidArgumentException;
use App\Bundle\RestBundle\Operation\PathSegmentNameGeneratorInterface;

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
    public function resolveOperationPath(string $resourceShortName, array $operation, $operationType, string $operationName = null): string
    {
        if (isset($operation['path'])) {
            return $operation['path'];
        }

        $path = '/'.$this->pathSegmentNameGenerator->getSegmentName($resourceShortName, true);

        if (OperationType::ITEM === $operationType) {
            $path .= '/{id}';
        }

        return $path;
    }
}
