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

        $this->addBodyListenerSection($node);
        $this->addExceptionToStatusSection($node);

        return $treeBuilder;
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
