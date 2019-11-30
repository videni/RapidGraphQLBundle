<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Type\Definition;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;
use Hashids\Hashids;

/**
 *  Hash id
 */
class HashIdType extends ScalarType
{
    /** @var string */
    public $name = 'HashId';

    /** @var string */
    public $description ='hash id';

    private $hashIds;

    public function __construct($prefix, $alias, $minHashLength = 6)
    {
        $salt = $prefix.$alias;
        $this->hashIds = new Hashids($salt, $minHashLength);
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
        return $this->hashIds->encode([$value]);
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

        $ids = $this->hashIds->decode($value);

        return count($ids) > 0 ? $ids[0]: null;
    }
}
