<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Factory;

use Symfony\Component\HttpFoundation\Request;

interface ParametersParserInterface
{
    /**
     * @param array $parameters
     * @param callable $getter
     *
     * @return array
     */
    public function parseRequestValues(array $parameters, callable $getter): array;

    public function parseRequestValueExpression(string $expression, callable $getter);
}

