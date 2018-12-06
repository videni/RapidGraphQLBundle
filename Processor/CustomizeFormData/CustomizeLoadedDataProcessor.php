<?php

namespace Videni\Bundle\RestBundle\Processor\CustomizeFormData;

use Oro\Component\ChainProcessor\ActionProcessor;

/**
 * The main processor for "customize_loaded_data" action.
 */
class CustomizeLoadedDataProcessor extends ActionProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function createContextObject()
    {
        return new CustomizeLoadedDataContext();
    }
}
