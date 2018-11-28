<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Routing\PathResolver;

use Videni\Bundle\RestBundle\Exception\InvalidArgumentException;
use Videni\Bundle\RestBundle\Operation\PathSegmentNameGeneratorInterface;
use Videni\Bundle\RestBundle\Operation\ActionTypes;
use Videni\Bundle\RestBundle\Config\Resource\OperationConfig;

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
    public function resolveOperationPath(string $resourceShortName, OperationConfig $operation, string $operationName = null): string
    {
        if ($path = $operation->getPath()) {
            return $path;
        }

        $path = '/'.$this->pathSegmentNameGenerator->getSegmentName($resourceShortName, true);
        if (ActionTypes::isSingleItemAction($operation->getAction())) {
            $path .= '/{id}';
        }

        return $path;
    }
}
