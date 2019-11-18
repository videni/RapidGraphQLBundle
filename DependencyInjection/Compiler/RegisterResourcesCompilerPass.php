<?php

namespace Videni\Bundle\RapidGraphQLBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Alias;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Common\Inflector\Inflector;
use Videni\Bundle\RapidGraphQLBundle\Config\Resource\ConfigProvider;
use Videni\Bundle\RapidGraphQLBundle\Config\Resource\Resource;
use Videni\Bundle\RapidGraphQLBundle\Factory\Factory;
use Videni\Bundle\RapidGraphQLBundle\Doctrine\ORM\ServiceEntityRepository;
use Videni\Bundle\RapidGraphQLBundle\Doctrine\ORM\EntityRepository;
use Videni\Bundle\RapidGraphQLBundle\Form\Type\AbstractResourceType;

class RegisterResourcesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $resourceProvider = $container->get(ConfigProvider::class);

        $resources = $resourceProvider->getAllResources();
        foreach ($resources as $resourceName => $resource) {
            //register entity class parameter
            $container->setParameter(sprintf('%s.class', $this->getServiceId($resource->getScope(), $resourceName, 'entity')), $resource->getEntityClass());

            $this->registerFactory($resourceName, $resource, $container);
            $this->registerRepository($resourceName, $resource, $container);
            $this->registerForm($resourceName, $resource, $container);
        }
    }

    private function registerFactory($resourceName, Resource $resource, $container)
    {
        $factoryClass = $resource->getFactoryClass();
        if(null === $factoryClass) {
            return;
        }

        $aliasId =  self::getServiceId($resource->getScope(), $resourceName, 'factory');

        $alias = new Alias($factoryClass);
        $alias->setPublic(true);
        if ($container->has($factoryClass) && $resource->getFactoryAutoAlias()) {
            $container->setAlias($aliasId, $alias);
             //don't register if a factory is associated with this resource
            return;
        }

        $container->setParameter(sprintf('%s.class', $aliasId), $factoryClass);

        $factoryDef = (new Definition($factoryClass))
            ->addArgument($resource->getEntityClass())
            ->setPublic(true)
            ->setAutoconfigured(true)
            ->setAutowired(true)
        ;

        //register it with class name as service name and also add an alias
        if ($factoryClass !== Factory::class && $resource->getFactoryAutoAlias()) {
            $container->setDefinition($factoryClass, $factoryDef);
            $container->setAlias($aliasId, $alias);
        } else {
            $container->setDefinition($aliasId, $factoryDef);
        }
    }

    private function registerRepository($resourceName, Resource $resource, $container)
    {
        $repositoryClass = $resource->getRepositoryClass();
        if(null === $repositoryClass) {
            return;
        }

        $aliasId = self::getServiceId($resource->getScope(), $resourceName, 'repository');
        $container->setParameter(sprintf('%s.class', $aliasId), $repositoryClass);

        $alias = new Alias($repositoryClass);
        $alias->setPublic(true);

        if ($container->has($repositoryClass) && $resource->getRepositoryAutoAlias()) {
            $container->setAlias($aliasId, $alias);

            return;
        }

        if (is_a($repositoryClass, ServiceEntityRepositoryInterface::class, true) && !$container->has($repositoryClass)) {
            throw new \Exception(sprintf('The repository %s is an instance of %s, please register it into service container yourself', $repositoryClass, ServiceEntityRepositoryInterface::class));
        }

        $definition = new Definition($repositoryClass);
        $definition
            ->setArguments([
                new Reference($this->getManagerServiceId($resource)),
                $this->getClassMetadataDefinition($resource->getEntityClass(), $resource),
            ])
            ->setPublic(true)
            ->setAutoconfigured(true)
            ->setAutowired(true)
        ;

        if (!in_array($repositoryClass, [ServiceEntityRepository::class, EntityRepository::class]) && $resource->getRepositoryAutoAlias()) {
            $container->setDefinition($repositoryClass, $definition);
            $container->setAlias($aliasId, $alias);
        } else {
            $container->setDefinition($aliasId, $definition);
        }
    }

    public function registerForm($resourceName, Resource $resource, $container)
    {
        $formClass = $resource->getFormClass();
        if(null === $formClass) {
            return;
        }

        $aliasId = self::getServiceId($resource->getScope(), $resourceName, 'form.type');
        $container->setParameter(sprintf('%s.class', $aliasId), $formClass);

        $alias = new Alias($formClass);
        $alias->setPublic(true);

        if ($container->has($formClass)) {
            $container->setAlias($aliasId, $alias);

            return;
        }

        $formDef = (new Definition($formClass))
            ->addTag('form.type')
            ->setPublic(true)
            ->setAutoconfigured(true)
            ->setAutowired(true)
        ;

        if (is_a($formClass, AbstractResourceType::class, true)) {
            $formDef
                ->addArgument($resource->getEntityClass())
                ->addArgument($resource->getFormValidationGroups())
           ;
        }

        $container->setDefinition($formClass, $formDef);
        $container->setAlias($aliasId, $alias);

        if(!$container->has($resource->getFormHandler())){
            throw new \Exception(sprintf('%s is not existed in container or it is private', $resource->getFormHandler()));
        }
    }

    protected function getClassMetadataDefinition($entityClass, Resource $resource): Definition
    {
        $definition = new Definition($this->getClassMetadataClassname());
        $definition
            ->setFactory([new Reference($this->getManagerServiceId($resource)), 'getClassMetadata'])
            ->setArguments([$entityClass])
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
    protected function getManagerServiceId(Resource $resource): string
    {
        return 'doctrine.orm.entity_manager';
    }

    private function getServiceId($scope, $resourceShortName, $key)
    {
         $name = Inflector::tableize($resourceShortName);

         return sprintf('%s.%s.%s', $scope, $key, $name);
    }
}
