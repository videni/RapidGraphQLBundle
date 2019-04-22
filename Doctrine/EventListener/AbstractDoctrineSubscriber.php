<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Doctrine\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\ReflectionService;
use Doctrine\Common\Persistence\Mapping\RuntimeReflectionService;
use Videni\Bundle\RestBundle\Config\Resource\ConfigProvider;
use Videni\Bundle\RestBundle\Model\ResourceInterface;

abstract class AbstractDoctrineSubscriber implements EventSubscriber
{
    protected $resourceConfigProvider;

    /**
     * @var RuntimeReflectionService
     */
    private $reflectionService;

    public function __construct(ConfigProvider $resourceConfigProvider)
    {
        $this->resourceConfigProvider = $resourceConfigProvider;
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

    protected function getReflectionService(): ReflectionService
    {
        if ($this->reflectionService === null) {
            $this->reflectionService = new RuntimeReflectionService();
        }

        return $this->reflectionService;
    }
}
