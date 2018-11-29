<?php

namespace Videni\Bundle\RestBundle\Processor;

use Oro\Component\ChainProcessor\ProcessorBagInterface;
use Oro\Component\ChainProcessor\ActionProcessor;
use Psr\Log\LoggerInterface;
use Videni\Bundle\RestBundle\Config\Resource\ResourceConfigProvider;
use Oro\Component\ChainProcessor\ContextInterface;

/**
 * The base processor for API actions that works with defined type of a resource.
 */
class RequestActionProcessor extends ActionProcessor
{
    protected $resourceConfigProvider;

     /** @var LoggerInterface */
    protected $logger;

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


    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

     /**
     * @param ContextInterface $context
     */
    protected function executeProcessors(ContextInterface $context)
    {
        $processors = $this->processorBag->getProcessors($context);
        /** @var ProcessorInterface $processor */
        foreach ($processors as $processor) {
            $processor->process($context);
        }
    }
}
