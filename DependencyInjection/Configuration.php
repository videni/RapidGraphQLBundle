<?php

namespace Videni\Bundle\RestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Videni\Bundle\RestBundle\Decoder\JsonDecoder;
use Symfony\Component\HttpFoundation\Response;

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
        ;

        $this->addFilterOperatorsNode($node);
        $this->addFiltersNode($node);
        $this->addBodyListenerSection($node);

        $this->addExceptionToStatusSection($node);

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

     /**
     * Adds an exception to status section.
     *
     * @throws InvalidConfigurationException
     */
    private function addExceptionToStatusSection(NodeBuilder $node)
    {
        $node
            ->arrayNode('exception_to_status')
                ->defaultValue([
                    ExceptionInterface::class => Response::HTTP_BAD_REQUEST,
                    InvalidArgumentException::class => Response::HTTP_BAD_REQUEST,
                    FilterValidationException::class => Response::HTTP_BAD_REQUEST,
                    OptimisticLockException::class => Response::HTTP_CONFLICT,
                ])
                ->info('The list of exceptions mapped to their HTTP status code.')
                ->normalizeKeys(false)
                ->useAttributeAsKey('exception_class')
                ->beforeNormalization()
                    ->ifArray()
                    ->then(function (array $exceptionToStatus) {
                        foreach ($exceptionToStatus as &$httpStatusCode) {
                            if (\is_int($httpStatusCode)) {
                                continue;
                            }

                            if (\defined($httpStatusCodeConstant = sprintf('%s::%s', Response::class, $httpStatusCode))) {
                                @trigger_error(sprintf('Using a string "%s" as a constant of the "%s" class is deprecated since API Platform 2.1 and will not be possible anymore in API Platform 3. Use the Symfony\'s custom YAML extension for PHP constants instead (i.e. "!php/const %s").', $httpStatusCode, Response::class, $httpStatusCodeConstant), E_USER_DEPRECATED);

                                $httpStatusCode = \constant($httpStatusCodeConstant);
                            }
                        }

                        return $exceptionToStatus;
                    })
                ->end()
                ->prototype('integer')->end()
                ->validate()
                    ->ifArray()
                    ->then(function (array $exceptionToStatus) {
                        foreach ($exceptionToStatus as $httpStatusCode) {
                            if ($httpStatusCode < 100 || $httpStatusCode >= 600) {
                                throw new InvalidConfigurationException(sprintf('The HTTP status code "%s" is not valid.', $httpStatusCode));
                            }
                        }

                        return $exceptionToStatus;
                    })
                ->end()
            ->end()
        ;
    }
}
