<?php

namespace App\Bundle\RestBundle\Processor;

use App\Bundle\RestBundle\Exception\InvalidArgumentException;
use App\Bundle\RestBundle\Utils\AttributesExtractor;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use App\Bundle\RestBundle\Processor\Context;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use App\Bundle\RestBundle\Metadata\Resource\ResourceMetadata;

final class SerializationProcessor implements ProcessorInterface
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

        $serializationContext = $this->createSerializationContext($context->getClassName(), $context->getOperationName(), $context->getMetadata());

        $requestContent = $context->getRequest()->getContent();

        $data = $this->serializer->deserialize(
            $requestContent,
            $context->getClassName(),
            $context->getFormat(),
            $serializationContext
        );

        $context->set('serialized_data', $data);
    }

    public function createSerializationContext($class, $operationName, ResourceMetadata $resourceMetadata)
    {
        $context = new SerializationContext();
        $context->setAttribute('api_operation_name', $operationName);

        $groups = $resourceMetadata->getOperationAttribute($operationName, 'normalization_context', [], true);

        if (isset($groups['groups'])) {
            $context->setGroups($groups['groups']);
        }

        $context->setAttribute('resource_class', $class);

        return $context;
    }
}
