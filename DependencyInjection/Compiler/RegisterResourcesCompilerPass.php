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
        $class = Factory::class;
        if ($factoryConfig && $factoryConfig->getClass()) {
            $class =  $factoryConfig->getClass();
        }

        $container->setParameter($factoryDefId, $class);

        $factoryDef = (new Definition($class))
            ->addArgument($className)
            ->setPublic(true)
        ;

        $container->setDefinition($factoryDefId, $factoryDef);
    }

    private function registerRepository($className, ResourceConfig $resourceConfig, $container)
    {
        /** @var ServiceConfig */
        $respositoryConfig = null;

        if ($respositoryConfig = $resourceConfig->getRepository()) {
            if ($respositoryConfig->getId() && $container->has($respositoryConfig->getId())) {
                return;
            }
        }

        $repositoryDefId = $respositoryConfig? $respositoryConfig->getId(): self::getServiceId($resourceConfig->getShortName(), 'repository');

        $class = ServiceEntityRepository::class;
        if ($respositoryConfig && $respositoryConfig->getClass()) {
            $class = $respositoryConfig->getClass();
        }

        $container->setParameter($repositoryDefId, $class);

        $repositoryDef = (new Definition($class))
            ->addArgument(new Reference(ManagerRegistry::class))
            ->addArgument($className)
            ->setPublic(true)
        ;

        $container->setDefinition($repositoryDefId, $repositoryDef);
    }

    private function getServiceId($resourceShortName, $key)
    {
         $name = Inflector::tableize($resourceShortName);

         return sprintf('app_rest.%s.%s.class', $key, $name);
    }
}
