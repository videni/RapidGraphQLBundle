<?php

namespace App\Bundle\RestBundle\Processor;

use App\Bundle\RestBundle\Exception\InvalidArgumentException;
use App\Bundle\RestBundle\Utils\AttributesExtractor;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\DeserializationContext;
use App\Bundle\RestBundle\Processor\Context;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

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

        $denormalizationContext = $this->createContext($context->getClassName(), $context->getOperationName(), $context->getMetadata());

        $data = $this->serializer->deserialize(
            $requestContent,
            $attributes['resource_class'],
            $context->getFormat(),
            $denormalizationContext
        );

        $context->set('data', $data);
    }



    public function createContext($class, $operationName, ResourceMetadata $resourceMetadata)
    {
        $factory = $resourceMetadata->getOperationAttribute($operationName, 'factory', [], true);

        $context->setAttribute('api_operation_name', $operationName);

        $groups = $resourceMetadata->getOperationAttribute($operationName, 'denormalization_context', [], true);
        if (isset($groups['groups'])) {
            $context->setGroups($groups['groups']);
        }

        $context->setAttribute('resource_class', $class);

        return $context;
    }
}
