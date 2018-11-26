<?php

namespace App\Bundle\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use App\Bundle\RestBundle\Config\Resource\ResourceConfigProvider;
use App\Bundle\RestBundle\Config\Resource\ResourceConfig;
use App\Bundle\RestBundle\Config\Resource\ServiceConfig;
use Doctrine\Common\Inflector\Inflector;
use App\Bundle\RestBundle\Factory\Factory;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class RegisterResourcesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $resourceConfigProvider = $container->get(ResourceConfigProvider::class);

        $resourceConfigs = $resourceConfigProvider->getAll();
        foreach ($resourceConfigs as $className => $resourceConfig) {
            $this->registerFactory($className, $resourceConfig, $container);
            $this->registerRepository($className, $resourceConfig, $container);
        }
    }

    private function registerFactory($className, ResourceConfig $resourceConfig, $container)
    {
         /** @var ServiceConfig */
        $factoryConfig = null;

        if ($factoryConfig = $resourceConfig->getFactory()) {
            if ($factoryConfig->getId() && $container->has($factoryConfig->getId())) {
                //don't register if a factory is associated with this resource
                return;
            }
        }

        $factoryDefId = $factoryConfig ? $factoryConfig->getId(): self::getServiceId($resourceConfig->getShortName(), 'factory');

        $container->setParameter($factoryDefId, Factory::class);

        $factoryDef = new Definition(Factory::class);
        $factoryDef->addArgument($className);

        $container->setDefinition($factoryDefId, $factoryDef);
    }

    private function registerRepository($className, ResourceConfig $resourceConfig, $container)
    {
        /** @var ServiceConfig */
        $respositoryConfig = null;

        if ($respositoryConfig = $resourceConfig->getRepository()) {
            if ($respositoryConfig->has('id') && $container->has($resourceConfig->getId())) {
                return;
            }
        }

        $repositoryDefId = $respositoryConfig? $respositoryConfig->getId(): self::getServiceId($resourceConfig->getShortName(), 'repository');

        $container->setParameter($repositoryDefId, ServiceEntityRepository::class);

        $repositoryDef = new Definition(ServiceEntityRepository::class);
        $repositoryDef->addArgument(new Reference(ManagerRegistry::class));
        $repositoryDef->addArgument($className);

        $container->setDefinition($repositoryDefId, $repositoryDef);
    }

    private function getServiceId($resourceShortName, $key)
    {
         $name = Inflector::tableize($resourceShortName);

         return sprintf('app_rest.%s.%s.class', $key, $name);
    }
}
