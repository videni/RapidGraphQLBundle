<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Metadata\Resource;

/**
 * Resource metadata.
 */
final class ResourceMetadata implements \Serializable
{
    private $shortName;
    private $description;
    private $collectionOperations;
    private $itemOperations;
    private $attributes;

    public function __construct(
        string $shortName = null,
        string $description = null,
        array $itemOperations = null,
        array $collectionOperations = null,
        array $attributes = null
    ) {
        $this->shortName = $shortName;
        $this->description = $description;
        $this->collectionOperations = $collectionOperations;
        $this->itemOperations = $itemOperations;
        $this->attributes = $attributes;
    }

    /**
     * Gets the short name.
     *
     * @return string|null
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * Gets the description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Gets operations.
     *
     * @return array|null
     */
    public function getItemOperations()
    {
        return $this->itemOperations;
    }

    /**
     * Gets collection operations.
     *
     * @return array|null
     */
    public function getCollectionOperations()
    {
        return $this->collectionOperations;
    }

    /**
     * Gets a collection operation attribute, optionally fallback to a resource attribute.
     */
    public function getCollectionOperationAttribute(string $operationName = null, string $key, $defaultValue = null, bool $resourceFallback = false)
    {
        return $this->findOperationAttribute($this->collectionOperations, $operationName, $key, $defaultValue, $resourceFallback);
    }

    /**
     * Gets an item operation attribute, optionally fallback to a resource attribute.
     */
    public function getItemOperationAttribute(string $operationName = null, string $key, $defaultValue = null, bool $resourceFallback = false)
    {
        return $this->findOperationAttribute($this->itemOperations, $operationName, $key, $defaultValue, $resourceFallback);
    }

    /**
     * Gets the first available operation attribute according to the following order: collection, item, optionally fallback to a default value.
     */
    public function getOperationAttribute(array $attributes, string $key, $defaultValue = null, bool $resourceFallback = false)
    {
        if (isset($attributes['collection_operation_name'])) {
            return $this->getCollectionOperationAttribute($attributes['collection_operation_name'], $key, $defaultValue, $resourceFallback);
        }

        if (isset($attributes['item_operation_name'])) {
            return $this->getItemOperationAttribute($attributes['item_operation_name'], $key, $defaultValue, $resourceFallback);
        }

        if ($resourceFallback && isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        return $defaultValue;
    }

     /**
     * Gets an operation attribute, optionally fallback to a resource attribute.
     */
    private function findOperationAttribute(array $operations = null, string $operationName = null, string $key, $defaultValue = null, bool $resourceFallback = false)
    {
        if (null !== $operationName && isset($operations[$operationName][$key])) {
            return $operations[$operationName][$key];
        }

        if ($resourceFallback && isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        return $defaultValue;
    }

    /**
     * Gets attributes.
     *
     * @return array|null
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Gets an attribute.
     */
    public function getAttribute(string $key, $defaultValue = null)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        return $defaultValue;
    }

    /**
     * @param mixed $attributes
     *
     * @return self
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function serialize()
    {
        return serialize(array(
        $this->shortName,
        $this->description,
        $this->collectionOperations,
        $this->itemOperations,
        $this->attributes
        ));
    }

    public function unserialize($str)
    {
        list(
        $this->shortName,
        $this->description,
        $this->collectionOperations,
        $this->itemOperations,
        $this->attributes
        ) = unserialize($str);
    }
}
