<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Videni\Bundle\RapidGraphQLBundle\Definition\Argument;
use Videni\Bundle\RapidGraphQLBundle\Config\Resource\Action;
use Videni\Bundle\RapidGraphQLBundle\Controller\ControllerResolver;
use Videni\Bundle\RapidGraphQLBundle\Security\ResourceAccessCheckerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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

    public function checkPermission($object, Action $action, Argument $argument)
    {
        $accessControl = $action->getAccessControl();
        if (null === $accessControl) {
            return;
        }
        $extraVariables =  array_merge($argument->getArrayCopy(), $argument->attributes->all());
        $extraVariables['object'] = $object;

        $isGranted = $this->resourceAccessChecker->isGranted($accessControl, $extraVariables);
        if (!$isGranted){
            throw new AccessDeniedHttpException($action->getAccessControlMessage() ?? 'Access Denied');
        }
    }
}
