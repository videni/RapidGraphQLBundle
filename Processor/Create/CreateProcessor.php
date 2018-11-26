<?php

namespace App\Bundle\RestBundle\Processor\Create;

use App\Bundle\RestBundle\Processor\RequestActionProcessor;

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
