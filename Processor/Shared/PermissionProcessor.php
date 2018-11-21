<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Processor\Shared;

use App\Bundle\RestBundle\Security\ResourceAccessCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Denies access to the current resource if the logged user doesn't have sufficient permissions.
 */
final class PermissionProcessor implements ProcessorInterface
{
    private $resourceAccessChecker;

    public function __construct(ResourceAccessCheckerInterface $resourceAccessChecker)
    {
        $this->resourceAccessChecker = $resourceAccessChecker;
    }

    /**
     * Sets the applicable format to the HttpFoundation Request.
     *
     *
     * @throws AccessDeniedException
     */
    public function process(ContextInterface $context)
    {
        $operationName = $context->getOperationName();
        $resourceMetadata = $context->getMetadata();

        $isGranted = $resourceMetadata->getOperationAttribute($operationName, 'access_control', null, true);
        if (null === $isGranted) {
            return;
        }

        $request = $context->getRequest();

        $extraVariables = $request->attributes->all();
        $extraVariables['object'] = $context->getResult();
        $extraVariables['request'] = $request;

        if (!$this->resourceAccessChecker->isGranted($context->getClassName(), $isGranted, $extraVariables)) {
            throw new AccessDeniedException($resourceMetadata->getOperationAttribute($operationName, 'access_control_message', 'Access Denied.', true));
        }
    }
}
