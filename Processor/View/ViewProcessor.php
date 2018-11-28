<?php

namespace Videni\Bundle\RestBundle\Processor\View;

use Videni\Bundle\RestBundle\Processor\RequestActionProcessor;

/**
 * The main processor for "view" action.
 */
class ViewProcessor extends RequestActionProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function createContextObject()
    {
        return new ViewContext($this->resourceConfigProvider);
    }
}
