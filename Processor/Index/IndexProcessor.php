<?php

namespace App\Bundle\RestBundle\Processor\Index;

use App\Bundle\RestBundle\Processor\RequestActionProcessor;

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
        return new IndexContext($this->resourceMetadataFatory, $this->paginatorConfigProvider);
    }
}
