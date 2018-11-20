<?php

namespace App\Bundle\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use App\Bundle\RestBundle\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Doctrine\Common\Inflector\Inflector;
use App\Bundle\RestBundle\Factory\Factory;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use App\Bundle\RestBundle\Metadata\Resource\ResourceMetadata;

class RegisterResourcesCompilerPass implements CompilerPassInterface
{
    private $resourceMetadataFactory;

    public function __construct(ResourceMetadataFactoryInterface $resourceMetadataFactory)
    {
        $this->resourceMetadataFactory = $resourceMetadataFactory;
    }

    public function process(ContainerBuilder $container)
    {
        $resourceMetadatas = $this->resourceMetadataFactory->getAllResourceMetadatas();
        foreach ($resourceMetadatas as $className => $metadata) {
            $this->registerFactory($className, $metadata, $container);
        }
    }

    private function registerFactory($className, ResourceMetadata $metadata, $container)
    {
        $$factoryDefId = self::getFactoryServiceId($metadata->getShortName());

        $container->setParameter($$factoryDefId, Factory::class);

        $factoryDef = new Definition(Factory::class);
        $factoryDef->addAgument($className);

        $container->setDefinition($factoryDefId, $factoryDef);
    }

    public static function getFactoryServiceId($resourceShortName)
    {
         $name = Inflector::tableize($resourceShortName);

         return sprintf('app_rest.factory.%s.class', $name);
    }
}
