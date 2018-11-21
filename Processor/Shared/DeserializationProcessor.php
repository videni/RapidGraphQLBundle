<?php

namespace App\Bundle\RestBundle\Processor\Shared;

use App\Bundle\RestBundle\Exception\InvalidArgumentException;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\DeserializationContext;
use App\Bundle\RestBundle\Processor\Context;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use App\Bundle\RestBundle\Metadata\Resource\ResourceMetadata;

final class DeserializationProcessor implements ProcessorInterface
{
    private $serializer;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

     /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var Context $context */

        $denormalizationContext = $this->createDenormalizationContext($context->getClassName(), $context->getOperationName(), $context->getMetadata());

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

    public function createDenormalizationContext($class, $operationName, ResourceMetadata $resourceMetadata)
    {
        $context = new DeserializationContext();

        $context->setAttribute('api_operation_name', $operationName);

        $groups = $resourceMetadata->getOperationAttribute($operationName, 'denormalization_context', [], true);

        if (isset($groups['groups'])) {
            $context->setGroups($groups['groups']);
        }

        $context->setAttribute('resource_class', $class);

        return $context;
    }
}
