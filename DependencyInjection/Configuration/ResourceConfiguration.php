<?php

namespace Videni\Bundle\RestBundle\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Doctrine\Common\Inflector\Inflector;
use Videni\Bundle\RestBundle\Doctrine\ORM\EntityRepository;
use Videni\Bundle\RestBundle\Factory\Factory;
use Videni\Bundle\RestBundle\Operation\ActionTypes;

class ResourceConfiguration implements ConfigurationInterface
{
    public const ROOT_NODE = "api";

    public const RESOURCE_PROVIDER_KEY = 'resource_provider';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(self::ROOT_NODE);
        $children = $rootNode->children();

        $children
            ->append($this->addResourceConfigurationSection())
        ;

        return $treeBuilder;
    }

    private function addResourceConfigurationSection()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('resources');

        $rootNode
            ->beforeNormalization()
                ->always(function ($v) {
                    foreach ($v as $resourceClass => &$value) {
                        if (!isset($value['short_name'])) {
                            $value['short_name'] = $this->getClassName($resourceClass);
                        }
                        if (!isset($value['scope'])) {
                            $value['scope'] = 'videni_rest';
                        }

                        if (isset($value['repository_class']) && !class_exists($value['repository_class'])) {
                            throw new \InvalidArgumentException(sprintf('repository_class %s of resource %s is not found', $value['repository_class'], $resourceClass));
                        }
                        if (isset($value['factory_class']) && !class_exists($value['factory_class'])) {
                            throw new \InvalidArgumentException(sprintf('factory_class %s of resource %s is not found', $value['factory_class'], $resourceClass));
                        }

                        $this->normalizeOperations($value['scope'], $value['short_name'], $value);
                    }

                    return $v;
                })
            ->end()
            ->validate()
                ->always(function ($v) {
                    foreach ($v as $resourceClass => &$value) {
                        if (!class_exists($resourceClass)) {
                            throw new \InvalidArgumentException(sprintf('Resource %s is supposed to be full quanlified class', $resourceClass));
                        }
                    }

                    return $v;
                })
            ->end()
            ->useAttributeAsKey('resource_class')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('route_prefix')->end()
                    ->scalarNode('short_name')->end()
                    ->scalarNode('scope')->defaultValue('videni_rest')->cannotBeEmpty()->end()
                    ->scalarNode('form')->end()
                    ->scalarNode('repository_class')->defaultValue(EntityRepository::class)->cannotBeEmpty()->end()
                    ->scalarNode('factory_class')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                    ->arrayNode('validation_groups')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('denormalization_context')
                        ->children()
                            ->arrayNode('groups')
                                ->prototype('variable')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('normalization_context')
                        ->children()
                            ->arrayNode('groups')
                                ->prototype('variable')->end()
                            ->end()
                            ->scalarNode('enable_max_depth')->defaultValue(false)->end()
                        ->end()
                    ->end()
                    ->arrayNode('operations')
                        ->useAttributeAsKey('name')
                        ->cannotBeEmpty()
                        ->arrayPrototype()
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) {
                                    return ['action' => $v];
                                })
                            ->end()
                            ->children()
                                ->scalarNode('path')->end()
                                ->scalarNode('grid')->end()
                                ->scalarNode('route_name')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('access_control')->end()
                                ->scalarNode('form')->end()
                                ->scalarNode('access_control_message')->end()
                                ->scalarNode('action')->end()
                                ->arrayNode('methods')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('defaults')
                                    ->performNoDeepMerging()
                                    ->variablePrototype()->end()
                                ->end()
                                ->arrayNode('requirements')
                                    ->performNoDeepMerging()
                                    ->variablePrototype()->end()
                                ->end()
                                ->arrayNode(self::RESOURCE_PROVIDER_KEY)
                                        ->beforeNormalization()
                                            ->ifString()
                                            ->then(function ($v) {
                                                return ['id' => $v];
                                            })
                                        ->end()
                                        ->children()
                                            ->scalarNode('id')->end()
                                            ->scalarNode('method')->end()
                                            ->scalarNode('spread_arguments')->defaultValue(true)->end()
                                            ->arrayNode('arguments')
                                                ->prototype('scalar')->end()
                                            ->end()
                                        ->end()
                                ->end()
                                ->arrayNode('validation_groups')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('denormalization_context')
                                    ->children()
                                        ->arrayNode('groups')
                                            ->prototype('variable')->end()
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('normalization_context')
                                    ->children()
                                        ->arrayNode('groups')
                                            ->prototype('variable')->end()
                                        ->end()
                                        ->scalarNode('enable_max_depth')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('factory')
                                    ->beforeNormalization()
                                        ->ifString()
                                        ->then(function ($v) {
                                            return ['id' => $v];
                                        })
                                    ->end()
                                    ->children()
                                        ->scalarNode('id')->end()
                                        ->scalarNode('method')->end()
                                        ->scalarNode('spread_arguments')->defaultValue(true)->end()
                                        ->arrayNode('arguments')
                                            ->prototype('scalar')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end()
        ;

        return $rootNode;
    }

    private function getClassName($fqcn)
    {
        if (false !== $pos = strrpos($fqcn, '\\')) {
            return substr($fqcn, $pos + 1);
        }

        return null;
    }

    private function normalizeOperations($scope, $resourceShortName, &$value)
    {
        if(!array_key_exists('operations', $value)) {
            return;
        }

        $defaultActions = [
            ActionTypes::UPDATE,
            ActionTypes::INDEX,
            ActionTypes::CREATE,
            ActionTypes::VIEW,
            ActionTypes::DELETE,
            ActionTypes::BULK_DELETE,
        ];

        foreach($value['operations'] as $operationName => &$actionConfig) {
            if (!isset($actionConfig['action'])) {
                if (!in_array($operationName, $defaultActions)) {
                    throw new \LogicException(
                        sprintf(
                            'There is no action type defined for operation %s of resource %s, none default operation must have action type defined',
                            $operationName,
                            $resourceShortName
                        )
                    );
                }

               $actionConfig = array_merge(
                    $actionConfig,
                    [
                        'action' => $operationName,
                    ]
                );
            } else if(!in_array($actionConfig['action'], $defaultActions)) {
                throw new \LogicException(
                    sprintf(
                        'Action type %s of operation %s of resource %s is not existed, only %s are supported', $actionConfig['action'],
                        $operationName,
                        $resourceShortName,
                        implode(',', $defaultActions)
                    )
                );
            }

            if (ActionTypes::INDEX === $actionConfig['action'] && !isset($actionConfig['grid'])) {
                throw new \LogicException(
                    sprintf(
                        'Grid is missing for resource %s %s operation, index action must have a grid defined.',
                        $resourceShortName,
                        $operationName
                    )
                );
            }
            if (ActionTypes::CREATE === $actionConfig['action']) {
                $this->setDefaultResourceProviderConfig($this->getServiceId($scope, $resourceShortName, 'factory'), $actionConfig);
            } else {
                $this->setDefaultResourceProviderConfig($this->getServiceId($scope, $resourceShortName, 'repository'), $actionConfig);
            }
        }
    }

    private function setDefaultResourceProviderConfig($serviceId, &$actionConfig)
    {
        $config = [
            "id" => $serviceId,
        ];

        $key = self::RESOURCE_PROVIDER_KEY;

        $actionConfig[$key] = isset($actionConfig[$key]) ?
            array_merge(
                $config,
                is_array($actionConfig[$key]) ? $actionConfig[$key]: ['id' => $actionConfig[$key]]
            ) : $config
        ;
    }

    private function getServiceId($scope, $resourceShortName, $key)
    {
         $name = Inflector::tableize($resourceShortName);

         return sprintf('%s.%s.%s', $scope, $key, $name);
    }
}
