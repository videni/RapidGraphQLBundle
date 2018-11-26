<?php

namespace App\Bundle\RestBundle\Processor\View;

use App\Bundle\RestBundle\Processor\RequestActionProcessor;

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
