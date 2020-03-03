<?php

namespace Videni\Bundle\RapidGraphQLBundle\Controller\ArgumentResolver;

use Videni\Bundle\RapidGraphQLBundle\Controller\ArgumentValueResolverInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class RootArgumentResolver implements ArgumentValueResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Argument $argument, ArgumentMetadata $argumentMetadata)
    {
        return isset($argument[$argumentMetadata->getName()]);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Argument $argument, ArgumentMetadata $argumentMetadata)
    {
        yield $argument[$argumentMetadata->getName()];
    }
}
