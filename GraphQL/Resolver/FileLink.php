<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Overblog\GraphQLBundle\Resolver\FieldResolver;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Pintushi\Bundle\FileBundle\Entity\AbstractFile;
use Doctrine\Common\Collections\Collection;

class FileLink implements ResolverInterface
{
    /** @var UrlGeneratorInterface */
    private $urlGenerator;
    private $expressionLanguage;

    public function __construct(UrlGeneratorInterface $urlGenerator, ExpressionLanguage $expressionLanguage)
    {
        $this->urlGenerator = $urlGenerator;
        $this->expressionLanguage = $expressionLanguage;
    }

    public function __invoke($value, $args, $context , $info, string $route, string $parameters = '[]', $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $rawParameters = json_decode($parameters, true);

        $object = FieldResolver::valueFromObjectOrArray($value, $info->fieldName);
        if (null == $object) {
            return;
        }

        if ($object instanceof Collection) {
            return array_map(function($file) use ($args, $route, $absolute, $rawParameters) {
                return $this->generate($file, $args, $route, $absolute, $rawParameters);
            }, $object->toArray());
        }

        return $this->generate($object, $args, $route, $absolute, $rawParameters);
    }

    protected function generate(object $file, $args, $route, $absolute, array $rawParameters)
    {
        if(!$file instanceof AbstractFile) {
            return null;
        }

        $evaluatedParams = [];
        foreach($rawParameters as $name => $parameter) {
            if(!is_scalar($parameter)) {
                continue;
            }
            if (is_string($parameter) &&  0 === \strpos($parameter, '@=')) {
                $evaluatedParams[$name] = $this->expressionLanguage->evaluate(substr($parameter, 2), [
                    'object' => $file,
                    'args' => $args
                ]);
            } else {
                $evaluatedParams[$name] = $parameter;
            }
        }

        return $this->urlGenerator->generate($route, $evaluatedParams, $absolute);
    }
}
