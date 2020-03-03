<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

final class NewPayload implements MutationInterface
{
    public function __invoke($name, $payload) {
        return [$name => $payload];
    }
}
