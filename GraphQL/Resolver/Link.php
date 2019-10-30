<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Overblog\GraphQLBundle\Resolver\FieldResolver;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class Link implements ResolverInterface
{
    /** @var UrlGeneratorInterface */
    private $urLGenerator;
    private $expressionLanguage;

    public function __construct(UrlGeneratorInterface $urLGenerator, ExpressionLanguage $expressionLanguage)
    {
        $this->urLGenerator = $urLGenerator;
        $this->expressionLanguage = $expressionLanguage;
    }

    public function __invoke($value, $args, $context , $info, string $route, string $parameters = '[]', $absolute = false)
    {
        $rawParameters = json_decode($parameters);
        $object = FieldResolver::valueFromObjectOrArray($value, $info->fieldName);
        if (null == $object) {
            return null;
        }

        $evaluatedParams = [];
        foreach($rawParameters as $name => $value) {
            if(!is_scalar($value)) {
                continue;
            }
            if (is_string($value) &&  0 === \strpos($value, '@=')) {
                $evaluatedParams[$name] = $this->expressionLanguage->evaluate(substr($value, 2), [
                    'object' => $object,
                    'args' => $args
                ]);
            } else {
                $evaluatedParams[$name] = $value;
            }
        }

        return $this->urLGenerator->generate($route, $evaluatedParams, (bool)$absolute);
    }
}
