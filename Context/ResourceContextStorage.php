<?php

namespace Videni\Bundle\RestBundle\Context;

class ResourceContextStorage
{
    private $context;

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param mixed $context
     *
     * @return self
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }
}
