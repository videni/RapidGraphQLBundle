<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Serializer;

use App\Bundle\RestBundle\Operation\OperationType;
use App\Bundle\RestBundle\Exception\RuntimeException;
use App\Bundle\RestBundle\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use App\Bundle\RestBundle\Utils\AttributesExtractor;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Context;

/**
 * {@inheritdoc}
 *
 */
final class SerializerContextBuilder implements SerializerContextBuilderInterface
{
    private $resourceMetadataFactory;
    private $resourceFactory;

    private $resourceMetadata;

    public function __construct(
        ResourceMetadataFactoryInterface $resourceMetadataFactory,
        ResourceFactoryInterface $resourceFactory
    ) {
        $this->resourceMetadataFactory = $resourceMetadataFactory;
        $this->resourceFactory = $resourceFactory;
    }

    public function createContext($class, $operationName, bool $normalization)
    {
        $this->resourceMetadata = $this->resourceMetadataFactory->create($attributes['resource_class']);

        $context = $normalization? new SerializationContext():  new DeserializationContext();
        if (!$normalization) {
            $factory = $this->resourceMetadata->getOperationAttribute($operationName, 'factory', [], true);
        }

        $context->setAttribute('api_operation_name', $operationName);

        $groups = $this->resourceMetadata->getOperationAttribute($operationName, 'denormalization_context', [], true);
        if (isset($groups['groups'])) {
            $context->setGroups($groups['groups']);
        }

        $context->setAttribute('resource_class', $attributes['resource_class']);

        return $context;
    }
}
