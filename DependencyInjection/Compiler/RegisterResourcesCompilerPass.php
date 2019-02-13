<?php

namespace Videni\Bundle\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Videni\Bundle\RestBundle\Config\Resource\ResourceConfigProvider;
use Videni\Bundle\RestBundle\Config\Resource\ResourceConfig;
use Videni\Bundle\RestBundle\Config\Resource\ServiceConfig;
use Doctrine\Common\Inflector\Inflector;
use Videni\Bundle\RestBundle\Factory\Factory;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Videni\Bundle\RestBundle\Doctrine\ORM\ServiceEntityRepository;
use Videni\Bundle\RestBundle\Doctrine\ORM\EntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Videni\Bundle\RestBundle\Util\DependencyInjectionUtil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;

class RegisterResourcesCompilerPass implements CompilerPassInterface
{
    private $applicationName;

    public function process(ContainerBuilder $container)
    {
        $bundleConifig = DependencyInjectionUtil::getConfig($container);

        $this->applicationName =  $bundleConifig['application_name'];

        $resourceConfigProvider = $container->get(ResourceConfigProvider::class);

        $resourceConfigs = $resourceConfigProvider->getAll();
        foreach ($resourceConfigs as $className => $resourceConfig) {
            //register entity class parameter
            $container->setParameter(sprintf('%s.class', $this->getServiceId($resourceConfig->getShortName(), 'entity')), $className);

            $this->registerFactory($className, $resourceConfig, $container);
            $this->registerRepository($className, $resourceConfig, $container);
        }
    }

    private function registerFactory($className, ResourceConfig $resourceConfig, $container)
    {
        $factoryClass = $resourceConfig->getFactoryClass();

        $alias =  self::getServiceId($resourceConfig->getShortName(), 'factory');
        if ($container->has($factoryClass)) {
             //don't register if a factory is associated with this resource
            return;
        }

        $container->setParameter(sprintf('%s.class', $alias), $factoryClass);

        $factoryDef = (new Definition($factoryClass))
            ->addArgument($className)
            ->setPublic(true)
        ;

        //register it with class name as service name and also add an alias
        if ($factoryClass !== Factory::class) {
            $container->setDefinition($factoryClass, $factoryDef);
            $container->setAlias($alias, $factoryClass);
        } else {
            $container->setDefinition($alias, $factoryDef);
        }
    }

    private function registerRepository($className, ResourceConfig $resourceConfig, $container)
    {
        $repositoryClass = $resourceConfig->getRepositoryClass();

        $alias = self::getServiceId($resourceConfig->getShortName(), 'repository');
        $container->setParameter(sprintf('%s.class', $alias), $repositoryClass);

        if ($container->has($repositoryClass)) {
            $container->setAlias($alias, $repositoryClass);

            return;
        }

        if (is_a($repositoryClass, ServiceEntityRepositoryInterface::class, true) && !$container->has($repositoryClass)) {
            throw new \RuntimeException('The repository %s is an instance of %1, please register it into service container yourself', $repositoryClass);
        }

        $definition = new Definition($repositoryClass);
        $definition->setArguments([
                new Reference($this->getManagerServiceId($resourceConfig)),
                $this->getClassMetadataDefinition($className, $resourceConfig),
            ])
            ->setPublic(true)
        ;

        if (!in_array($repositoryClass, [ServiceEntityRepository::class, EntityRepository::class])) {
            $container->setDefinition($repositoryClass, $definition);
            $container->setAlias($alias, $repositoryClass);
        } else {
            $container->setDefinition($alias, $definition);
        }
    }

    private function getServiceId($resourceShortName, $key)
    {
         $name = Inflector::tableize($resourceShortName);

         return sprintf('%s.%s.%s', $this->applicationName, $key, $name);
    }

    protected function getClassMetadataDefinition($className, ResourceConfig $resourceConfig): Definition
    {
        $definition = new Definition($this->getClassMetadataClassname());
        $definition
            ->setFactory([new Reference($this->getManagerServiceId($resourceConfig)), 'getClassMetadata'])
            ->setArguments([$className])
            ->setPublic(false)
        ;

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    protected function getClassMetadataClassname(): string
    {
        return 'Doctrine\\ORM\\Mapping\\ClassMetadata';
    }

     /**
     * {@inheritdoc}
     */
    protected function getManagerServiceId(ResourceConfig $resourceConfig): string
    {
        return 'doctrine.orm.entity_manager';
    }
}
