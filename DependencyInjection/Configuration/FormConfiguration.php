<?php

namespace Videni\Bundle\RestBundle\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Oro\Component\EntitySerializer\ConfigUtil;

class FormConfiguration
{
    private $maxNestingLevel;

    public function __construct($maxNestingLevel = 0)
    {
        $this->maxNestingLevel = $maxNestingLevel;
    }

    public function configure()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder
            ->root('forms')
        ;

        $fieldsNode = $this->doConfigure($rootNode);

        for (; $this->maxNestingLevel > 0; $this->maxNestingLevel--) {
            $fieldsNode = $this->doConfigure($fieldsNode);
        }

        return $rootNode;
    }

    protected function doConfigure(ArrayNodeDefinition $node)
    {
        $formNode = $node
            ->useAttributeAsKey('entity_class')
            ->validate()
                ->always(function ($v) {
                    foreach ($v as $key => &$value) {
                        if (!class_exists($key)) {
                            throw new \InvalidArgumentException(sprintf('Entity %s is supposed to be full quanlified class', $key));
                        }
                    }

                    return $v;
                })
            ->end()
            ->arrayPrototype()
                ->children()
        ;

        $this->configureFormNode($formNode);

        $fieldsNode = $formNode
            ->arrayNode('fields')
                ->useAttributeAsKey('name')
                ->normalizeKeys(false)
                ->arrayPrototype()
                    ->children();

        $this->configureFieldNode($fieldsNode);

        return $fieldsNode;
    }

      /**
     * @param NodeBuilder $node
     */
    protected function configureFormNode(NodeBuilder $node): void
    {
        $node
            ->enumNode(ConfigUtil::EXCLUSION_POLICY)
                ->values([ConfigUtil::EXCLUSION_POLICY_ALL, ConfigUtil::EXCLUSION_POLICY_NONE])
            ->end()
            ->variableNode('post_serialize')->end()
            ->scalarNode('form_type')->end()
            ->arrayNode('form_options')
                ->useAttributeAsKey('name')
                ->performNoDeepMerging()
                ->prototype('variable')->end()
            ->end()
            ->variableNode('form_event_subscriber')
                ->validate()
                    ->always(function ($v) {
                        if (\is_string($v)) {
                            return [$v];
                        }
                        if (\is_array($v)) {
                            return $v;
                        }
                        throw new \InvalidArgumentException(
                            'The value must be a string or an array.'
                        );
                    })
                ->end()
            ->end()
        ;
    }

    protected function configureFieldNode(NodeBuilder $node): void
    {
        $node
            ->booleanNode('exclude')->end()
            ->scalarNode('description')->cannotBeEmpty()->end()
            ->scalarNode('property_path')->cannotBeEmpty()->end()
            ->scalarNode('data_type')->cannotBeEmpty()->end()
            ->scalarNode('target_class')->end()
            ->enumNode('target_type')
                ->values(['to-many', 'to-one', 'collection'])
            ->end()
            ->booleanNode('collapse')->end()
            ->scalarNode('form_type')->end()
            ->arrayNode('form_options')
                ->useAttributeAsKey('name')
                ->performNoDeepMerging()
                ->prototype('variable')->end()
            ->end()
            ->arrayNode('depends_on')
                ->prototype('scalar')->end()
            ->end()
        ;
    }
}
