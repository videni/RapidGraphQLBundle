<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Processor\Shared;

use Videni\Bundle\RestBundle\Security\ResourceAccessCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Videni\Bundle\RestBundle\Config\Resource\ResourceConfig;

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
        $resourceConfig = $context->getResourceConfig();

        $operationConfig = $resourceConfig->getOperation($operationName);

        $isGranted = $operationConfig->getAccessControll();
        if (null === $isGranted) {
            return;
        }

        $request = $context->getRequest();

        $extraVariables = $request->attributes->all();
        $extraVariables['object'] = $context->getResult();
        $extraVariables['request'] = $request;

        if (!$this->resourceAccessChecker->isGranted($context->getClassName(), $isGranted, $extraVariables)) {
            throw new AccessDeniedException($operationConfig->getAccessControllMessage()?? 'Access Denied');
        }
    }
}
