<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Factory;

use Symfony\Component\HttpFoundation\Request;

interface ParametersParserInterface
{
    /**
     * @param array $parameters
     * @param Request $request
     *
     * @return array
     */
    public function parseRequestValues(array $parameters, Request $request): array;
}
