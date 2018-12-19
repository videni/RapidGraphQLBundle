<?php

namespace Videni\Bundle\RestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Videni\Bundle\RestBundle\Decoder\JsonDecoder;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('videni_rest');

        $node = $rootNode
            ->children()
                ->scalarNode('application_name')
                    ->defaultValue('videni_rest')
                ->end()
        ;

        $this->addFilterOperatorsNode($node);
        $this->addFiltersNode($node);
        $this->addBodyListenerSection($node);

        return $treeBuilder;
    }

    /**
     * @param NodeBuilder $node
     */
    private function addFilterOperatorsNode(NodeBuilder $node)
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

      /**
     * @param NodeBuilder $node
     */
    private function addFiltersNode(NodeBuilder $node)
    {
        $node
            ->arrayNode('filters')
                ->info('A definition of filters')
                ->example(
                    [
                        'integer' => [
                            'supported_operators' => ['=', '!=', '<', '<=', '>', '>=', '*', '!*']
                        ],
                        'primaryField' => [
                            'class' => 'Videni\Bundle\RestBundle\Filter\PrimaryFieldFilter'
                        ],
                        'association' => [
                            'factory' => ['@videni_rest.filter_factory.association', 'createFilter']
                        ]
                    ]
                )
                ->useAttributeAsKey('name')
                ->prototype('array')
                    ->validate()
                        ->always(function ($value) {
                            if (empty($value['factory'])) {
                                unset($value['factory']);
                                if (empty($value['class'])) {
                                    $value['class'] = 'Videni\Bundle\RestBundle\Filter\ComparisonFilter';
                                }
                            }

                            return $value;
                        })
                    ->end()
                    ->validate()
                        ->ifTrue(function ($value) {
                            return !empty($value['class']) && !empty($value['factory']);
                        })
                        ->thenInvalid('The "class" and "factory" should not be used together.')
                    ->end()
                    ->children()
                        ->scalarNode('class')
                            ->cannotBeEmpty()
                        ->end()
                        ->arrayNode('factory')
                            ->validate()
                                ->ifTrue(function ($value) {
                                    return count($value) !== 2 || 0 !== strpos($value[0], '@');
                                })
                                ->thenInvalid('Expected [\'@serviceId\', \'methodName\']')
                            ->end()
                            ->prototype('scalar')->cannotBeEmpty()->end()
                        ->end()
                        ->arrayNode('supported_operators')
                            ->prototype('scalar')->end()
                            ->cannotBeEmpty()
                            ->defaultValue(['=', '!=', '*', '!*'])
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addBodyListenerSection(NodeBuilder $node)
    {
        $decodersDefaultValue = ['json' => JsonDecoder::class];

        $node
            ->arrayNode('body_listener')
                ->fixXmlConfig('decoder', 'decoders')
                ->addDefaultsIfNotSet()
                ->canBeUnset()
                ->canBeDisabled()
                ->children()
                    ->scalarNode('service')->defaultNull()->end()
                    ->scalarNode('default_format')->defaultNull()->end()
                    ->booleanNode('throw_exception_on_unsupported_content_type')
                        ->defaultFalse()
                    ->end()
                    ->arrayNode('decoders')
                        ->useAttributeAsKey('name')
                        ->defaultValue($decodersDefaultValue)
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('array_normalizer')
                        ->addDefaultsIfNotSet()
                        ->beforeNormalization()
                            ->ifString()->then(function ($v) {
                                return ['service' => $v];
                            })
                        ->end()
                        ->children()
                            ->scalarNode('service')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
