<?php

namespace App\Bundle\RestBundle\Processor\Shared;

use App\Bundle\RestBundle\Exception\InvalidArgumentException;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\DeserializationContext;
use App\Bundle\RestBundle\Processor\Context;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use App\Bundle\RestBundle\Config\Resource\ResourceConfig;

final class DeserializationProcessor implements ProcessorInterface
{
    private $serializer;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

     /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var Context $context */

        $denormalizationContext = $this->createDenormalizationContext($context->getClassName(), $context->getOperationName(), $context->getResourceConfig());

        $denormalizationContext->setAttribute('target', $context->getResult());

        $requestContent = $context->getRequest()->getContent();

        $data = $this->serializer->deserialize(
            $requestContent,
            $context->getClassName(),
            $context->getFormat(),
            $denormalizationContext
        );

        $context->setResult($data);
    }

    public function createDenormalizationContext($class, $operationName, ResourceConfig $resourceConfig)
    {
        $context = new DeserializationContext();

        $context->setAttribute('api_operation_name', $operationName);

        if ($denormalizationContext = $resourceConfig->getOperationAttribute($operationName, 'denormalization_context')) {
            $context->setGroups($denormalizationContext->getGroups());
        }

        $context->setAttribute('resource_class', $class);

        return $context;
    }
}
