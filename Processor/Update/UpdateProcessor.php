<?php

namespace App\Bundle\RestBundle\Processor\Update;

use App\Bundle\RestBundle\Processor\RequestActionProcessor;

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
        return new UpdateContext($this->resourceMetadataFatory);
    }
}
