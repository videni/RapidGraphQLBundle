<?php

namespace App\Bundle\RestBundle\Processor\Delete;

use App\Bundle\RestBundle\Processor\RequestActionProcessor;

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
