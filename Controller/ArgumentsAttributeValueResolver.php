<?php

namespace Videni\Bundle\RapidGraphQLBundle\Controller;

use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use  Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ArgumentsAttributeValueResolver implements ArgumentValueResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return !$argument->isVariadic() &&
            $request->attributes->has('arguments') &&
            isset($request->attributes->get('arguments')[$argument->getName()])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        yield $request->attributes->get('arguments')[$argument->getName()];
    }
}
