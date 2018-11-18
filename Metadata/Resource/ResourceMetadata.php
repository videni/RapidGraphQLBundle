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
    private $operations;
    private $attributes;

    public function __construct(
        string $shortName = null,
        string $description = null,
        array $operations = null,
        array $attributes = null
    ) {
        $this->shortName = $shortName;
        $this->description = $description;
        $this->operations = $operations;
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
    public function getOperations()
    {
        return $this->operations;
    }


     /**
     * Gets an operation attribute, optionally fallback to a resource attribute.
     */
    public function getOperationAttribute(string $operationName, string $key, $defaultValue = null, bool $resourceFallback = false)
    {
        if (isset($this->operations[$operationName][$key])) {
            return $this->operations[$operationName][$key];
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
        $this->operations,
        $this->attributes
        ));
    }

    public function unserialize($str)
    {
        list(
        $this->shortName,
        $this->description,
        $this->operations,
        $this->attributes
        ) = unserialize($str);
    }
}
