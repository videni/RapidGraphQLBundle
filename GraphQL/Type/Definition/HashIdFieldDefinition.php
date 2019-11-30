<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Type\Definition;

use Overblog\GraphQLBundle\Definition\Builder\MappingInterface;

class HashIdFieldDefinition implements MappingInterface
{
    public function toMappingDefinition(array $config): array
    {
        $format = $config['format'] ?? \DateTime::ATOM;

        return [
            'type' => "String",
            'resolve' => sprintf('@=resolver("datetime", [value, info, "%s"])', $format)
        ];
    }
}
