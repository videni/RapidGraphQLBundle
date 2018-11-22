<?php

namespace App\Bundle\RestBundle\DependencyInjection;

use App\Bundle\RestBundle\Filter\FilterOperatorRegistry;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

class PaginatorConfiguration implements ConfigurationInterface
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
        $rootNode = $treeBuilder->root(self::ROOT_NODE);

        $this->addPaginatorConfiguration($rootNode);

        return $treeBuilder;
    }

     /**
     * @param ArrayNodeDefinition $node
     */
    private function addPaginatorConfiguration(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('paginators')
                    ->useAttributeAsKey('code')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('extends')->cannotBeEmpty()->end()
                            ->arrayNode('sortings')
                                ->performNoDeepMerging()
                                ->useAttributeAsKey('name')
                                ->enumPrototype()->values(['asc', 'desc'])->cannotBeEmpty()->end()
                            ->end()
                            ->scalarNode('page_size')->defaultValue(15)->end()
                            ->arrayNode('sorters')
                                ->useAttributeAsKey('name')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('description')->end()
                                        ->scalarNode('property_path')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('filters')
                                ->useAttributeAsKey('name')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('date_type')->isRequired()->cannotBeEmpty()->end()
                                        ->scalarNode('description')->end()
                                        ->scalarNode('allow_array')->defaultFalse()->end()
                                        ->scalarNode('allow_range')->defaultFalse()->end()
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
            ->end()
        ;
    }
}
