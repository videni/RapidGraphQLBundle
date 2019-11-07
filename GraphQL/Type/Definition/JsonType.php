<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Type\Definition;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;

/**
 * Turn string in to json
 */
class JsonType extends ScalarType
{
    /** @var string */
    public $name = 'Json';

    /** @var string */
    public $description ='The JSON scalar type';

    /**
     * @param mixed $value
     *
     * @return mixed|string
     *
     * @throws Error
     */
    public function serialize($value)
    {
        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return string
     *
     */
    public function parseValue($value)
    {
       return $value;
    }

    /**
     * @param Node         $valueNode
     * @param mixed[]|null $variables
     *
     * @return string|null
     *
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        return $valueNode->value;
    }
}
