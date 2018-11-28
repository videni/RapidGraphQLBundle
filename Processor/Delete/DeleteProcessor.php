<?php

namespace Videni\Bundle\RestBundle\Processor\Delete;

use Videni\Bundle\RestBundle\Processor\RequestActionProcessor;

/**
 * The main processor for "create" action.
 */
class DeleteProcessor extends RequestActionProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function createContextObject()
    {
        return new DeleteContext($this->resourceConfigProvider);
    }
}
