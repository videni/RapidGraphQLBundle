<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\EventListener;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Mapping\ReflectionService;
use Doctrine\Common\Persistence\Mapping\RuntimeReflectionService;
use Videni\Bundle\RestBundle\Config\Resource\ResourceConfigProvider;
use Videni\Bundle\RestBundle\Model\ResourceInterface;

final class ORMMappedSuperClassSubscriber implements EventSubscriber
{
    protected $resourceConfigProvider;

     /**
     * @var RuntimeReflectionService
     */
    private $reflectionService;

    public function __construct(ResourceConfigProvider $resourceConfigProvider)
    {
        $this->resourceConfigProvider = $resourceConfigProvider;
    }

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
        $metadata = $eventArgs->getClassMetadata();

        $this->convertToEntityIfNeeded($metadata);

        if (!$metadata->isMappedSuperclass) {
            $this->setAssociationMappings($metadata, $eventArgs->getEntityManager()->getConfiguration());
        } else {
            $this->unsetAssociationMappings($metadata);
        }
    }

    protected function getReflectionService(): ReflectionService
    {
        if ($this->reflectionService === null) {
            $this->reflectionService = new RuntimeReflectionService();
        }

        return $this->reflectionService;
    }

    /**
     * @param ClassMetadata $metadata
     *
     * @return bool
     */
    protected function isResource(ClassMetadata $metadata): bool
    {
        if (!$reflClass = $metadata->getReflectionClass()) {
            return false;
        }

        return $reflClass->implementsInterface(ResourceInterface::class);
    }

    /**
     * @param ClassMetadataInfo $metadata
     */
    private function convertToEntityIfNeeded(ClassMetadataInfo $metadata): void
    {
        if (false === $metadata->isMappedSuperclass) {
            return;
        }

        try {
            $resourceConfig = $this->resourceConfigProvider->get($metadata->getName());
        } catch (\InvalidArgumentException $exception) {
            return;
        }

        $metadata->isMappedSuperclass = false;
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @param Configuration $configuration
     */
    private function setAssociationMappings(ClassMetadataInfo $metadata, Configuration $configuration): void
    {
        $class = $metadata->getName();
        if (!class_exists($class)) {
            return;
        }
        foreach (class_parents($class) as $parent) {
            if (false === in_array($parent, $configuration->getMetadataDriverImpl()->getAllClassNames(), true)) {
                continue;
            }

            $parentMetadata = new ClassMetadata(
                $parent,
                $configuration->getNamingStrategy()
            );

            // Wakeup Reflection
            $parentMetadata->wakeupReflection($this->getReflectionService());

            // Load Metadata
            $configuration->getMetadataDriverImpl()->loadMetadataForClass($parent, $parentMetadata);

            if (false === $this->isResource($parentMetadata)) {
                continue;
            }

            if ($parentMetadata->isMappedSuperclass) {
                foreach ($parentMetadata->getAssociationMappings() as $key => $value) {
                    if ($this->isRelation($value['type']) && !isset($metadata->associationMappings[$key])) {
                        $metadata->associationMappings[$key] = $value;
                    }
                }
            }
        }
    }

    /**
     * @param ClassMetadataInfo $metadata
     */
    private function unsetAssociationMappings(ClassMetadataInfo $metadata): void
    {
        if (false === $this->isResource($metadata)) {
            return;
        }

        foreach ($metadata->getAssociationMappings() as $key => $value) {
            if ($this->isRelation($value['type'])) {
                unset($metadata->associationMappings[$key]);
            }
        }
    }

    /**
     * @param int $type
     *
     * @return bool
     */
    private function isRelation(int $type): bool
    {
        return in_array(
            $type,
            [
                ClassMetadataInfo::MANY_TO_MANY,
                ClassMetadataInfo::ONE_TO_MANY,
                ClassMetadataInfo::ONE_TO_ONE,
            ],
            true
        );
    }
}
