<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Type\Definition;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;

class FormSchemaType extends ScalarType
{
    /** @var string */
    public $name = 'FormSchema';

    /** @var string */
    public $description ='The `FormSchema` scalar type represents json form schema.';

    /**
     * @param mixed $value
     *
     * @return mixed|string
     *
     * @throws Error
     */
    public function serialize($value)
    {
        return \json_decode($value);
    }

    /**
     * @param mixed $value
     *
     * @return string
     *
     * @throws Error
     */
    public function parseValue($value)
    {
        throw new Exception();
    }

    /**
     * @param Node         $valueNode
     * @param mixed[]|null $variables
     *
     * @return string|null
     *
     * @throws Exception
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        // Intentionally without message, as all information already in wrapped Exception
        throw new Exception();
    }
}
