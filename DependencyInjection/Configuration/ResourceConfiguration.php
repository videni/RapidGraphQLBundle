<?php

namespace Videni\Bundle\RestBundle\DependencyInjection\Configuration;

use Videni\Bundle\RestBundle\Filter\FilterOperatorRegistry;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Videni\Bundle\RestBundle\Operation\ActionTypes;
use Doctrine\Common\Inflector\Inflector;
use Videni\Bundle\RestBundle\Doctrine\ORM\EntityRepository;
use Videni\Bundle\RestBundle\Factory\Factory;

class ResourceConfiguration implements ConfigurationInterface
{
    public const ROOT_NODE = "api";
    public const MAX_RESULTS = 50;

    public const DEFAULT_PAGINATOR_NAME = 'default';

    /** @var FilterOperatorRegistry */
    private $filterOperatorRegistry;

    private $applicationName;

    /**
     * @param FilterOperatorRegistry $filterOperatorRegistry
     */
    public function __construct(FilterOperatorRegistry $filterOperatorRegistry, $applicationName)
    {
        $this->filterOperatorRegistry = $filterOperatorRegistry;
        $this->applicationName = $applicationName;
    }

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

                        $default = [
                            self::DEFAULT_PAGINATOR_NAME => [
                                'max_results' => self::MAX_RESULTS
                            ]
                        ];

                        if (isset($value['repository_class']) && !class_exists($value['repository_class'])) {
                            throw new \InvalidArgumentException(sprintf('repository_class %s of resource %s is not found', $value['repository_class'], $resourceClass));
                        }
                        if (isset($value['factory_class']) && !class_exists($value['factory_class'])) {
                            throw new \InvalidArgumentException(sprintf('factory_class %s of resource %s is not found', $value['factory_class'], $resourceClass));
                        }

                        //set 'default' paginator for each resource
                        if(!array_key_exists('paginators', $value)) {
                            $value['paginators'] = $default;
                        } else if (!array_key_exists('default', $value['paginators'])) {
                            $value['paginators'] = $value['paginators'] + $default;
                        }

                        $this->normalizeOperations($value['short_name'], $value);
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
                                ->scalarNode('paginator')->end()
                                ->scalarNode('route_name')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('access_control')->end()
                                ->scalarNode('acl_enabled')->defaultValue(false)->end()
                                ->scalarNode('resource_provider')->end()
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
                                ->arrayNode('repository')
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
                    ->append($this->addPaginatorConfigurationSection())
                ->end()
            ->end()
        ->end()
        ;

        return $rootNode;
    }

    private function addPaginatorConfigurationSection()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('paginators');

        $rootNode
            ->useAttributeAsKey('paginator_name')
            ->arrayPrototype()
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('max_results')->defaultValue(self::MAX_RESULTS)->end()
                    ->scalarNode('disable_sorting')->defaultValue(false)->end()
                    ->arrayNode('sortings')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) {
                                    return ['order' => $v];
                                })
                            ->end()
                            ->children()
                                ->scalarNode('description')->end()
                                ->scalarNode('property_path')->end()
                                ->enumNode('order')->values(['asc', 'desc'])->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('filters')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('type')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('description')->end()
                                ->scalarNode('allow_array')->defaultFalse()->end()
                                ->scalarNode('allow_range')->defaultFalse()->end()
                                ->scalarNode('property_path')->end()
                                ->scalarNode('collection')->defaultFalse()->end()
                                ->scalarNode('position')->defaultValue(100)->end()
                                ->arrayNode('options')
                                    ->performNoDeepMerging()
                                    ->variablePrototype()->end()
                                ->end()
                                ->arrayNode('operators')
                                    ->validate()
                                        ->always(function ($value) {
                                            if (\is_array($value) && !empty($value)) {
                                                $operators = [];
                                                foreach ($value as $val) {
                                                    $operators[] = $this->filterOperatorRegistry->resolveOperator($val);
                                                }
                                                $value = $operators;
                                            }

                                            return $value;
                                        })
                                    ->end()
                                    ->prototype('scalar')->end()
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

    private function normalizeOperations($resourceShortName, &$value)
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
                    throw new \LogicException(sprintf('There is no action type defined for operation %s, None default operation must have action type defined', $operationName));
                }

               $actionConfig = array_merge(
                    $actionConfig,
                    [
                        'action' => $operationName,
                    ]
                );
            } else if(!in_array($actionConfig['action'], $defaultActions)) {
                throw new \LogicException(sprintf('Action type %s of operation %s is not existed, only %s are supported', $actionConfig['action'], $operationName, implode(',', $defaultActions)));
            }

            if (ActionTypes::INDEX === $actionConfig['action'] && !isset($actionConfig['paginator'])) {
                $actionConfig['paginator'] =  self::DEFAULT_PAGINATOR_NAME;
            }

            $this->setDefaultServiceConfig($resourceShortName, 'repository', $actionConfig);
            $this->setDefaultServiceConfig($resourceShortName, 'factory', $actionConfig);
        }
    }

    private function setDefaultServiceConfig($resourceShortName, $key, &$actionConfig)
    {
        $config = [
            "id" =>  $this->getServiceId($resourceShortName, $key),
        ];

        $actionConfig[$key] = isset($actionConfig[$key])?  array_merge($config, $actionConfig[$key]) : $config;
    }

    private function getServiceId($resourceShortName, $key)
    {
         $name = Inflector::tableize($resourceShortName);

         return sprintf('%s.%s.%s', $this->applicationName, $key, $name);
    }
}
