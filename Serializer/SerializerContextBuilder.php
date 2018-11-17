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

    public function __construct(ResourceMetadataFactoryInterface $resourceMetadataFactory, ResourceFactoryInterface $resourceFactory)
    {
        $this->resourceMetadataFactory = $resourceMetadataFactory;
        $this->resourceFactory = $resourceFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRequest(Request $request, bool $normalization, array $attributes): Context
    {
        $resourceMetadata = $this->resourceMetadataFactory->create($attributes['resource_class']);

        $context = $normalization ? new SerializationContext(): new DeserializationContext();

        $key = $normalization ? 'normalization_context' : 'denormalization_context';

        $operationKey = null;
        $operationType = null;
        $factory = [];
        $groups = null;

        if (isset($attributes['collection_operation_name'])) {
            $operationKey = 'collection_operation_name';
            $operationType = OperationType::COLLECTION;

            $groups = $resourceMetadata->getCollectionOperationAttribute($attributes[$operationKey], $key, [], true);
            $factory = $resourceMetadata->getCollectionOperationAttribute($attributes[$operationKey], 'factory', [], true);

        } else {
            $operationKey = 'item_operation_name';
            $operationType = OperationType::ITEM;

            $groups = $resourceMetadata->getItemOperationAttribute($attributes[$operationKey], $key, [], true);
            $factory = $resourceMetadata->getCollectionOperationAttribute($attributes[$operationKey], 'factory', [], true);
        }

        if ($key === "denormalization_context") {
            $object = $this->resourceFactory->create($request, ['class' => $attributes['resource_class']] + $factory);
            $context->setAttribute('object_to_update', $object);
        }

        if (isset($groups['groups'])) {
            $context->setGroups($groups['groups']);
        }

        $context->setAttribute($operationKey, $attributes[$operationKey]);
        $context->setAttribute('operation_type', $operationType);

        if (!$normalization && !$context->hasAttribute('api_allow_update')) {
            $context->setAttribute('api_allow_update', \in_array($request->getMethod(), ['PUT', 'PATCH'], true));
        }

        $context->setAttribute('resource_class', $attributes['resource_class']);
        $context->setAttribute('request_uri', $request->getRequestUri());
        $context->setAttribute('uri', $request->getUri());

        return $context;
    }
}
