<?php

namespace Videni\Bundle\RapidGraphQLBundle\EventListener;

use Videni\Bundle\RapidGraphQLBundle\Util\DoctrineHelper;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Videni\Bundle\RapidGraphQLBundle\Context\ResourceContextStorage;
use Videni\Bundle\RapidGraphQLBundle\Security\ResourceAccessCheckerInterface;
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
        $actionConfig = $context->getAction();

        $accessControl = $actionConfig->getAccessControl();
        if (null === $accessControl) {
            return;
        }

        $request = $event->getRequest();

        $extraVariables = $request->attributes->all();
        $extraVariables['object'] = $request->attributes->get('data');
        $extraVariables['request'] = $request;

        $isGranted = $this->resourceAccessChecker->isGranted($accessControl, $extraVariables);
        if (!$isGranted){
            throw new AccessDeniedException($actionConfig->getAccessControlMessage() ?? 'Access Denied');
        }
    }
}
