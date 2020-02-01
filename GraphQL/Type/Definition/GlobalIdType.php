<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Type\Definition;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;
use Overblog\GraphQLBundle\Relay\Node\GlobalId;

class GlobalIdType extends ScalarType
{
    /** @var string */
    public $name = 'GlobalId';

    /** @var string */
    public $description ='global id';

    private $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @param mixed $value
     *
     * @return mixed|string
     *
     * @throws Error
     */
    public function serialize($value)
    {
        return GlobalId::toGlobalId($this->type, $value);
    }

    /**
     * @param mixed $value
     *
     * @return string
     *
     */
    public function parseValue($value)
    {
       return $this->decode($value);
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
        return $this->decode($valueNode->value);
    }

    private function decode($value)
    {
        if (empty($value)) {
            return null;
        }

        $globalId = GlobalId::fromGlobalId($value);
        if ($globalId['type'] !== $this->type) {
            throw new \LogicException(sprintf(
                'Decode global id %s failed, Type %s is required, but got %s',
                $value,
                $this->type,
                $globalId['type']
            ));
        }

        return $globalId['id'];
    }
}
