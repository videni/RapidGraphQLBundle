<?php

namespace Videni\Bundle\RestBundle\Processor\Index;

use Videni\Bundle\RestBundle\Processor\RequestActionProcessor;

/**
 * The main processor for "get_list" action.
 */
class IndexProcessor extends RequestActionProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function createContextObject()
    {
        return new IndexContext($this->resourceConfigProvider);
    }
}
