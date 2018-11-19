<?php

namespace App\Bundle\RestBundle\Processor;

use Oro\Component\ChainProcessor\ProcessorBagInterface;
use Oro\Component\ChainProcessor\ActionProcessor;
use Psr\Log\LoggerInterface;
use App\Bundle\RestBundle\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;

/**
 * The base processor for API actions that works with defined type of a resource.
 */
class RequestActionProcessor extends ActionProcessor
{
    protected $resourceMetadataFatory;

    /**
     * @param ProcessorBagInterface $processorBag
     * @param string                $action
     */
    public function __construct(
        ProcessorBagInterface $processorBag,
        $action,
        ResourceMetadataFactoryInterface $resourceMetadataFatory
    ) {
        parent::__construct($processorBag, $action);

        $this->resourceMetadataFatory = $resourceMetadataFatory;
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
