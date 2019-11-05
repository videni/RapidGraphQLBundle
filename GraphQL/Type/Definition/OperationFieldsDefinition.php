<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Type\Definition;

use Overblog\GraphQLBundle\Definition\Builder\MappingInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\OptionsResolver\Options;

final class OperationFieldsDefinition implements MappingInterface
{
    public function toMappingDefinition(array $config): array
    {
        $options = $this->configureOptions($config);
        $description = $options['description'] ?? 'Related operations';

        $operations = $options['operations'];
        $namePrefix = \preg_replace('/(.*)?Operation/', '$1', $options['typeName']);
        $typeName = $namePrefix.'Operation';

        $mapping = [];
        $mapping['fields']['operations'] =  [
            'type' =>  $typeName,
            'resolve' => '@=value',
            'public' => $options['public']
        ];

        $fields = [];
        foreach($operations as $operation) {
            $fields[$operation['operationName']] = [
                'type' => 'Boolean',
                'resolve' => $operation['resolve'],
                'public' => $operation['public']?? true
            ];
        }

        $mapping['types'][$typeName] = [
            'type' => 'object',
            'config' => [
                'description' => $description,
                'fields' => $fields
            ]
        ];

        return $mapping;
    }

    protected function configureOptions(array $options)
    {
        $optionResolver = new OptionsResolver();
        $optionResolver
            ->setRequired(['typeName', 'operations'])
            ->setAllowedTypes('typeName', ['string'])
            ->setDefaults([
                'public' => true
            ])
            ->setDefined(['description'])
            ->setAllowedTypes('description', ['string', 'null'])
            ->setAllowedTypes('public', ['string', 'null', 'bool'])
            ->setAllowedTypes('operations', ['array'] )
            ->setNormalizer(
                'operations',
                function (Options $options, $operations) {
                    if (count($operations) == 0) {
                        throw new InvalidConfigurationException('The option "$operations" must not be empty.');
                    }

                    //@todo: Validate operation structure
                    return $operations;
                }
            )
        ;

        return $optionResolver->resolve($options);
    }
}
