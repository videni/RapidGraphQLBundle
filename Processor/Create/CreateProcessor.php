<?php

namespace Videni\Bundle\RestBundle\Processor\Create;

use Videni\Bundle\RestBundle\Processor\RequestActionProcessor;

/**
 * The main processor for "create" action.
 */
class CreateProcessor extends RequestActionProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function createContextObject()
    {
        return new CreateContext($this->resourceConfigProvider);
    }
}
