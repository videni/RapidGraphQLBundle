<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\Factory;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Webmozart\Assert\Assert;

final class ParametersParser implements ParametersParserInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ExpressionLanguage
     */
    private $expression;

    /**
     * @param ContainerInterface $container
     * @param ExpressionLanguage $expression
     */
    public function __construct(ContainerInterface $container, ExpressionLanguage $expression)
    {
        $this->container = $container;
        $this->expression = $expression;
    }

    /**
     * {@inheritdoc}
     */
    public function parseRequestValues(array $parameters, callable $getter): array
    {
        return array_map(function ($parameter) use ($getter) {
            if (is_array($parameter)) {
                return $this->parseRequestValues($parameter, $getter);
            }

            return $this->parseRequestValue($parameter, $getter);
        }, $parameters);
    }

     /**
     * @param string $expression
     * @param callable $getter
     *
     * @return mixed
     */
    public function parseRequestValueExpression(string $expression, callable $getter)
    {
        $expression = preg_replace_callback('/(\$\w+)/', function ($matches) use ($getter) {
            $variable = \call_user_func($getter, substr($matches[1], 1));

            if (is_array($variable) || is_object($variable)) {
                throw new \InvalidArgumentException(sprintf(
                    'Cannot use %s ($%s) as parameter in expression.',
                    gettype($variable),
                    $matches[1]
                ));
            }

            return is_string($variable) ? sprintf('"%s"', $variable) : $variable;
        }, $expression);

        return $this->expression->evaluate($expression, ['container' => $this->container]);
    }

    /**
     * @param mixed $parameter
     * @param callable $getter
     *
     * @return mixed
     */
    private function parseRequestValue($parameter, callable $getter)
    {
        if (!is_string($parameter)) {
            return $parameter;
        }

        if (0 === strpos($parameter, '$')) {
            return \call_user_func($getter, substr($parameter, 1));
        }

        if (0 === strpos($parameter, 'expr:')) {
            return $this->parseRequestValueExpression(substr($parameter, 5), $getter);
        }

        if (0 === strpos($parameter, '!!')) {
            return $this->parseRequestValueTypecast($parameter, $getter);
        }

        return $parameter;
    }

    /**
     * @param mixed $parameter
     * @param callable $getter
     *
     * @return mixed
     */
    private function parseRequestValueTypecast($parameter, callable $getter)
    {
        [$typecast, $castedValue] = explode(' ', $parameter, 2);

        $castFunctionName = substr($typecast, 2) . 'val';

        Assert::oneOf($castFunctionName, ['intval', 'floatval', 'boolval'], 'Variable can be casted only to int, float or bool.');

        return $castFunctionName($this->parseRequestValue($castedValue, $getter));
    }
}
