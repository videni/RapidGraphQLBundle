<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Doctrine\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

final class ORMRepositoryClassSubscriber extends AbstractDoctrineSubscriber
{
    /**
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $this->setCustomRepositoryClass($eventArgs->getClassMetadata());
    }

    /**
     * @param ClassMetadata $metadata
     */
    private function setCustomRepositoryClass(ClassMetadata $metadata): void
    {
        try {
            $resourceConfig = $this->resourceConfigProvider->getResourceByClassName($metadata->getName());
        } catch (\InvalidArgumentException $exception) {
            return;
        }

        $repositoryClass = $resourceConfig->getRepositoryClass();

        $metadata->setCustomRepositoryClass($repositoryClass);
    }
}
