<?php

namespace Videni\Bundle\RestBundle\Processor\BulkDelete;

use Videni\Bundle\RestBundle\Processor\RequestActionProcessor;

/**
 * The main processor for "create" action.
 */
class BulkDeleteProcessor extends RequestActionProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function createContextObject()
    {
        return new BulkDeleteContext($this->resourceConfigProvider);
    }
}
