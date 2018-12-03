<?php

namespace Videni\Bundle\RestBundle\Processor;

use Oro\Component\ChainProcessor\ActionProcessor;

/**
 * The main processor for "customize_form_data" action.
 */
class CustomizeFormDataProcessor extends ActionProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function createContextObject()
    {
        return new CustomizeFormDataContext();
    }
}
