<?php

namespace Videni\Bundle\RapidGraphQLBundle\Controller;

use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Overblog\GraphQLBundle\Definition\Argument;

interface ArgumentValueResolverInterface
{
    /**
     * Whether this resolver can resolve the value for the given ArgumentMetadata.
     *
     * @return bool
     */
    public function supports(Argument $argument, ArgumentMetadata $argumentMetadata);

    /**
     * Returns the possible value(s).
     *
     * @return iterable
     */
    public function resolve(Argument $argument, ArgumentMetadata $argumentMetadata);
}
