<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Type\Definition;

use Overblog\GraphQLBundle\Definition\Builder\MappingInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileLinkFieldDefinition implements MappingInterface
{
    public function toMappingDefinition(array $config): array
    {
        $options = $this->configureOptions($config);

        $route = $options['route'] ?? null;
        $routeParams = $options['parameters'] ?? [];
        $absolute = $options['absolute'];

        return [
            'type' => "Json",
            'resolve' => sprintf("@=resolver('fileLink', [value, args, context, info, '%s', '%s', %d])", $route, json_encode($routeParams), $absolute)
        ];
    }

    protected function configureOptions(array $options)
    {
        $optionResolver = new OptionsResolver();
        $optionResolver
            ->setRequired(['route'])
            ->setDefault('absolute', false)
            ->setDefined(['absolute', 'parameters'])
            ->setAllowedTypes('absolute', ['bool'])
            ->setAllowedTypes('parameters', ['array'] )
        ;

        return $optionResolver->resolve($options);
    }
}
