<?php

namespace Videni\Bundle\RestBundle\EventListener;

use Videni\Bundle\RestBundle\Util\DoctrineHelper;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Videni\Bundle\RestBundle\Context\ResourceContextStorage;
use Videni\Bundle\RestBundle\Security\ResourceAccessCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DenyAccessListener
{
    private $contextStorage;
    private $resourceAccessChecker;

    public function __construct(
        ResourceContextStorage $contextStorage,
        ResourceAccessCheckerInterface $resourceAccessChecker
    ) {
        $this->contextStorage = $contextStorage;
        $this->resourceAccessChecker = $resourceAccessChecker;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if(!$event->isMasterRequest()) {
            return;
        }

        $context = $this->contextStorage->getContext();
        if (null === $context) {
            return;
        }
        $operationConfig = $context->getOperationConfig();

        $accessControl = $operationConfig->getAccessControl();
        if (null === $accessControl) {
            return;
        }

        $request = $event->getRequest();

        $extraVariables = $request->attributes->all();
        $extraVariables['object'] = $request->attributes->get('data');
        $extraVariables['request'] = $request;

        $isGranted = $this->resourceAccessChecker->isGranted($accessControl, $extraVariables);
        if (!$isGranted){
            throw new AccessDeniedException($operationConfig->getAccessControlMessage() ?? 'Access Denied');
        }
    }
}
