<?php

namespace Videni\Bundle\RestBundle\Event;

use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;
use Videni\Bundle\RestBundle\Config\Resource\ResourceConfig;

interface EventDispatcherInterface
{
    public function dispatchPreEvent(
        string $eventName,
        ResourceConfig $resourceConifig,
        $resource
    ): ResourceEvent;

    public function dispatchPostEvent(
        string $eventName,
        ResourceConfig $resourceConifig,
        $resource
    ): ResourceEvent;
}
