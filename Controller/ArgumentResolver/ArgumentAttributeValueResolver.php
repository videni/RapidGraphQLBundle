<?php

namespace Videni\Bundle\RapidGraphQLBundle\Controller\ArgumentResolver;

use Videni\Bundle\RapidGraphQLBundle\Controller\ArgumentValueResolverInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ArgumentAttributeValueResolver implements ArgumentValueResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Argument $argument, ArgumentMetadata $argumentMetadata)
    {
        return $argument->attributes->has($argumentMetadata->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Argument $argument, ArgumentMetadata $argumentMetadata)
    {
        yield $argument->attributes->get($argumentMetadata->getName());
    }
}
