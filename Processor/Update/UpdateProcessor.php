<?php

namespace Videni\Bundle\RestBundle\Processor\Update;

use Videni\Bundle\RestBundle\Processor\RequestActionProcessor;

/**
 * The main processor for "update" action.
 */
class UpdateProcessor extends RequestActionProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function createContextObject()
    {
        return new UpdateContext($this->resourceConfigProvider);
    }
}
