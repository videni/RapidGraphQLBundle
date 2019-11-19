<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RapidGraphQLBundle\Config\Resource\Action;
use Videni\Bundle\RapidGraphQLBundle\Controller\ControllerResolver;
use Videni\Bundle\RapidGraphQLBundle\Security\ResourceAccessCheckerInterface;

abstract class AbstractResolver {

    protected $resourceContextResolver;
    protected $controllerExecutor;
    protected $controllerResolver;
    protected $resourceAccessChecker;

    public function __construct(
        ResourceContextResolver $resourceContextResolver,
        ControllerResolver $controllerResolver,
        ControllerExecutor $controllerExecutor,
        ResourceAccessCheckerInterface $resourceAccessChecker
    ) {
        $this->resourceContextResolver = $resourceContextResolver;
        $this->controllerResolver = $controllerResolver;
        $this->controllerExecutor = $controllerExecutor;
        $this->resourceAccessChecker = $resourceAccessChecker;
    }

    public function checkPermission($object, Action $action, Request $request)
    {
        $accessControl = $action->getAccessControl();
        if (null === $accessControl) {
            return;
        }

        $extraVariables = $request->attributes->all();
        $extraVariables['object'] = $object;
        $extraVariables['request'] = $request;

        $isGranted = $this->resourceAccessChecker->isGranted($accessControl, $extraVariables);
        if (!$isGranted){
            throw new AccessDeniedException($action->getAccessControlMessage() ?? 'Access Denied');
        }
    }
}
