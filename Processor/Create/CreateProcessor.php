<?php

namespace App\Bundle\RestBundle\Processor\Create;

use App\Bundle\RestBundle\Processor\RequestActionProcessor;
use App\Bundle\RestBundle\Processor\NormalizeResultContext;

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
        return new CreateContext($this->resourceMetadataFatory);
    }
}
