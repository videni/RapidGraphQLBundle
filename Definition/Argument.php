<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\Definition;

use Overblog\GraphQLBundle\Definition\Argument as BaseArgument;
use Symfony\Component\HttpFoundation\ParameterBag;

class Argument extends BaseArgument
{
    public $attributes;

    public function __construct(?array $rawArguments = null, $attributes = [])
    {
        parent::__construct($rawArguments);

        $this->attributes = new ParameterBag($attributes);
    }
}
