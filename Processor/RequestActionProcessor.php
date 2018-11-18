<?php

namespace App\Bundle\RestBundle\Processor;

use Oro\Component\ChainProcessor\ProcessorBagInterface;

/**
 * The base processor for API actions that works with defined type of a resource.
 */
class RequestActionProcessor extends NormalizeResultActionProcessor
{
    /**
     * @param ProcessorBagInterface $processorBag
     * @param string                $action
     */
    public function __construct(
        ProcessorBagInterface $processorBag,
        $action
    ) {
        parent::__construct($processorBag, $action);
    }
}
