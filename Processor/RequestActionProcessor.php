<?php

namespace App\Bundle\RestBundle\Processor;

use Oro\Component\ChainProcessor\ProcessorBagInterface;
use Oro\Component\ChainProcessor\ActionProcessor;
use Psr\Log\LoggerInterface;
use App\Bundle\RestBundle\Config\Resource\ResourceConfigProvider;

/**
 * The base processor for API actions that works with defined type of a resource.
 */
class RequestActionProcessor extends ActionProcessor
{
    protected $resourceConfigProvider;

    /**
     * @param ProcessorBagInterface $processorBag
     * @param string                $action
     */
    public function __construct(
        ProcessorBagInterface $processorBag,
        $action,
        ResourceConfigProvider $resourceConfigProvider
    ) {
        parent::__construct($processorBag, $action);

        $this->resourceConfigProvider = $resourceConfigProvider;
    }

    /** @var LoggerInterface */
    protected $logger;

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
