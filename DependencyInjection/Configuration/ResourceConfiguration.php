<?php

namespace Videni\Bundle\RestBundle\DependencyInjection\Configuration;

use Videni\Bundle\RestBundle\Filter\FilterOperatorRegistry;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Videni\Bundle\RestBundle\Operation\ActionTypes;
use Doctrine\Common\Inflector\Inflector;

class ResourceConfiguration implements ConfigurationInterface
{
    public const ROOT_NODE = "api";
    public const MAX_RESULTS = 50;


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
                    foreach ($v as $key => &$value) {
                        if (!isset($value['short_name'])) {
                            $value['short_name'] = $this->getClassName($key);
                        }
                        $this->setDefaultService($value, 'repository');
                        $this->setDefaultService($value, 'factory');

                        //set 'default' paginator for each resource
                        if(!array_key_exists('paginators', $value) || !array_key_exists('default', $value['paginators'])) {
                            $value['paginators'] = [
                                    'default' => [
                                        'max_results' => self::MAX_RESULTS
                                    ]
                                ]
                            ;
                        }
                    }

                    return $v;
                })
            ->end()
            ->validate()
                ->always(function ($v) {
                    foreach ($v as $key => &$value) {
                        if (!class_exists($key)) {
                            throw new \InvalidArgumentException(sprintf('Resource %s is supposed to be full quanlified class', $key));
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
                            ->scalarNode('class')->end()
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
                            ->scalarNode('class')->end()
                            ->scalarNode('spread_arguments')->defaultValue(true)->end()
                            ->arrayNode('arguments')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('operations')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->beforeNormalization()
                                ->ifTrue(function ($value) {
                                    return $value['action'] === ActionTypes::INDEX  && empty($value['paginator']);
                                })
                                ->then(function ($v) {
                                    $v['paginator'] = 'default';

                                    return $v;
                                })
                            ->end()
                            ->children()
                                ->scalarNode('path')->end()
                                ->scalarNode('paginator')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('access_control')->end()
                                ->scalarNode('acl_enabled')->defaultValue(false)->end()
                                ->scalarNode('resource_provider')->end()
                                ->scalarNode('form')->end()
                                ->scalarNode('access_control_message')->end()
                                ->scalarNode('action')->isRequired()->cannotBeEmpty()->end()
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

    private function setDefaultService(&$value, $attributeName)
    {
        $serviceId = $this->getServiceId($value['short_name'], $attributeName);

        if (!isset($value[$attributeName])) {
            $value[$attributeName] = $serviceId;
        } else if (!isset($value[$attributeName]['id'])) {
            $value[$attributeName] = array_merge(
                ['id' => $serviceId],
                is_string($value[$attributeName]) ? ['id' => $value[$attributeName]] : $value[$attributeName]
            );
        }
    }

    private function getServiceId($resourceShortName, $key)
    {
        $name = Inflector::tableize($resourceShortName);

        return sprintf('%s.%s.%s', $this->applicationName, $key, $name);
    }
}
