<?php

namespace Videni\Bundle\RestBundle\Event;

use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;
use Videni\Bundle\RestBundle\Config\Resource\Resource;

interface EventDispatcherInterface
{
    public function dispatchPreEvent(
        string $eventName,
        Resource $resourceConifig,
        $resource
    ): ResourceEvent;

    public function dispatchPostEvent(
        string $eventName,
        Resource $resourceConifig,
        $resource
    ): ResourceEvent;
}
