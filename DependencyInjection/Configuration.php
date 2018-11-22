<?php

namespace App\Bundle\RestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('app_rest');

        $node = $rootNode->children();

        $this->addActionsNode($node);
        $this->appendFilterOperatorsNode($node);

        return $treeBuilder;
    }

    /**
     * @param NodeBuilder $node
     */
    private function addActionsNode(NodeBuilder $node)
    {
        $node
            ->arrayNode('actions')
                ->info('A definition of Data API actions')
                ->example(
                    [
                        'get' => [
                            'processor_service_id' => 'app_rest.get.processor',
                            'processing_groups' => [
                                'intialize' => [
                                    'priority' => -10
                                ],
                                'security' => [
                                    'priority' => -20
                                ]
                            ]
                        ]
                    ]
                )
                ->useAttributeAsKey('name')
                ->prototype('array')
                    ->validate()
                        ->always(function ($value) {
                            if (!empty($value['processing_groups'])) {
                                $priority = 0;
                                foreach ($value['processing_groups'] as &$group) {
                                    if (!isset($group['priority'])) {
                                        $priority--;
                                        $group['priority'] = $priority;
                                    }
                                }
                            }

                            return $value;
                        })
                    ->end()
                    ->children()
                        ->scalarNode('processor_service_id')
                            ->info('The service id of the action processor. Set for public actions only.')
                            ->cannotBeEmpty()
                        ->end()
                        ->arrayNode('processing_groups')
                            ->info('A list of groups by which child processors can be split')
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('priority')
                                        ->info('The priority of the group.')
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param NodeBuilder $node
     */
    private function appendFilterOperatorsNode(NodeBuilder $node)
    {
        $node
            ->arrayNode('filter_operators')
                ->info(
                    'A definition of operators for filters.'
                    . ' The key is the name of an operator.'
                    . ' The value is optional and it is a short name of an operator.'
                )
                ->example([
                    'eq'     => '=',
                    'regexp' => null
                ])
                ->useAttributeAsKey('name')
                ->prototype('scalar')
                ->end()
            ->end();
    }
}
