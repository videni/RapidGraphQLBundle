<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL;

class ExecutorContextStorage
{
    private $value;

    /**
     * @return mix
     */
    public function get(string $name)
    {
        return $this->value[$name] ?? null;
    }

    /**
     * @param \ArrayObject $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}
