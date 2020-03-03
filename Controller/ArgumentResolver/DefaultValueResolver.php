<?php

namespace Videni\Bundle\RapidGraphQLBundle\Controller\ArgumentResolver;

use Overblog\GraphQLBundle\Definition\Argument;
use Videni\Bundle\RapidGraphQLBundle\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Yields the default value defined in the action signature when no value has been given.
 */
final class DefaultValueResolver implements ArgumentValueResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Argument $argument, ArgumentMetadata $metadata): bool
    {
        return $metadata->hasDefaultValue() || (null !== $metadata->getType() && $metadata->isNullable() && !$metadata->isVariadic());
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Argument $argument, ArgumentMetadata $metadata): iterable
    {
        yield $metadata->hasDefaultValue() ? $metadata->getDefaultValue() : null;
    }
}
