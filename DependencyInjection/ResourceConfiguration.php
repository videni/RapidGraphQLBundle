<?php

namespace App\Bundle\RestBundle\DependencyInjection;

use App\Bundle\RestBundle\Filter\FilterOperatorRegistry;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use App\Bundle\RestBundle\Operation\ActionTypes;
use Doctrine\Common\Inflector\Inflector;

class ResourceConfiguration implements ConfigurationInterface
{
    public const ROOT_NODE = "api";

    /** @var FilterOperatorRegistry */
    private $filterOperatorRegistry;

    /**
     * @param FilterOperatorRegistry $filterOperatorRegistry
     */
    public function __construct(FilterOperatorRegistry $filterOperatorRegistry)
    {
        $this->filterOperatorRegistry = $filterOperatorRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder
            ->root(self::ROOT_NODE)
                ->children()
                    ->append($this->addPaginatorConfigurationSection())
                    ->append($this->addResourceConfigurationSection())
                ->end()
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
                        if (!isset($value['repository'])) {
                            $value['repository'] = $this->getServiceId($value['short_name'], 'repository');
                        } else if (!isset($value['repository']['id'])) {
                            $value['repository'] = array_merge(
                                ['id' => $this->getServiceId($value['short_name'], 'repository')],
                                is_string($value['repository']) ? ['id' => $value['repository']] : $value['repository']
                            );
                        }

                        if (!isset($value['factory'])) {
                            $value['factory'] = $this->getServiceId($value['short_name'], 'factory');
                        } else if (!isset($value['factory']['id'])) {
                            $value['factory'] = array_merge(
                                ['id' => $this->getServiceId($value['short_name'], 'factory')],
                                is_string($value['factory']) ? ['id' => $value['factory']] : $value['factory']
                            );
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
            ->useAttributeAsKey('class')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('route_prefix')->end()
                    ->scalarNode('short_name')->end()
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
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('normalization_context')
                        ->children()
                            ->arrayNode('groups')
                                ->prototype('scalar')->end()
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
                            ->arrayNode('arguments')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('operations')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->validate()
                                ->ifTrue(function ($value) {
                                    return $value['action'] === ActionTypes::INDEX  && empty($value['paginator']);
                                })
                                ->thenInvalid('Paginator is required for index action')
                            ->end()
                            ->children()
                                ->scalarNode('path')->end()
                                ->scalarNode('paginator')->end()
                                ->scalarNode('access_control')->end()
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
                                            ->scalarNode('class')->end()
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
                                            ->prototype('scalar')->end()
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('normalization_context')
                                    ->children()
                                        ->arrayNode('groups')
                                            ->prototype('scalar')->end()
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

    private function addPaginatorConfigurationSection()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('paginators');

        $rootNode
            ->useAttributeAsKey('code')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('class')->cannotBeEmpty()->end()
                    ->scalarNode('page_size')->defaultValue(15)->end()
                    ->scalarNode('max_results')->defaultValue(50)->end()
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

    private function getServiceId($resourceShortName, $key)
    {
        $name = Inflector::tableize($resourceShortName);

        return sprintf('app_rest.%s.%s', $key, $name);
    }
}
