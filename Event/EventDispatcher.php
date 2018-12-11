<?php

namespace Videni\Bundle\RestBundle\Event;

use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;
use Videni\Bundle\RestBundle\Config\Resource\ResourceConfig;
use Doctrine\Common\Inflector\Inflector;

final class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var SymfonyEventDispatcherInterface
     */
    private $eventDispatcher;

    private $applicationName;

    /**
     * @param SymfonyEventDispatcherInterface $eventDispatcher
     */
    public function __construct(SymfonyEventDispatcherInterface $eventDispatcher, $applicationName)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->applicationName = $applicationName;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatchPreEvent(
        string $eventName,
        ResourceConfig $resourceConifig,
        $resource
    ): ResourceEvent {
        $event = new ResourceEvent($resource);

        $this->eventDispatcher->dispatch(sprintf('%s.%s.pre_%s', $this->applicationName, Inflector::tableize($resourceConifig->getShortName()), $eventName), $event);

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatchPostEvent(
        string $eventName,
        ResourceConfig $resourceConifig,
        $resource
    ): ResourceEvent {
        $event = new ResourceEvent($resource);

        $this->eventDispatcher->dispatch(sprintf('%s.%s.post_%s', $this->applicationName, Inflector::tableize($resourceConifig->getShortName()), $eventName), $event);

        return $event;
    }
}
