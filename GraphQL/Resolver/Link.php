<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Link implements ResolverInterface
{
    /** @var UrlGeneratorInterface */
    private $urLGenerator;

    public function __construct(UrlGeneratorInterface $urLGenerator)
    {
        $this->urLGenerator = $urLGenerator;
    }

    public function __invoke($value, $args, $context , $info, string $route = null, array $parameters = [], $absolute = false)
    {
        // dump($value, $args, $context , $info);exit;
        //@todo evalue expressions in parameters with variables($value, $args);

        return $value;
        // return $route ? $this->urLGenerator->generate($route, $parameters, $absolute): null;
    }
}
