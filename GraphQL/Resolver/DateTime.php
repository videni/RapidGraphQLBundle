<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Resolver\FieldResolver;

class DateTime implements ResolverInterface
{
    public function __invoke($value, ResolveInfo $info, string $format)
    {
        $datetime = FieldResolver::valueFromObjectOrArray($value, $info->fieldName);

        return $datetime instanceof \DateTime ? $datetime->format($format): null;
    }
}
