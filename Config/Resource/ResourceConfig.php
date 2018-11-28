<?php

namespace Videni\Bundle\RestBundle\Config\Resource;

class ResourceConfig
{
    private $routePrefix;
    private $shortName;
    private $description;
    private $formats = [];

    private $factory = null;
    private $repository = null;
    private $normalizationContext = null;
    private $denormalizationContext = null;
    private $operations = [];

    /**
     * @return mixed
     */
    public function getRoutePrefix()
    {
        return $this->routePrefix;
    }

    /**
     * @param mixed $routePrefix
     *
     * @return self
     */
    public function setRoutePrefix($routePrefix)
    {
        $this->routePrefix = $routePrefix;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * @param mixed $shortName
     *
     * @return self
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormats()
    {
        return $this->formats;
    }

    /**
     * @param mixed $formats
     *
     * @return self
     */
    public function setFormats($formats)
    {
        $this->formats = $formats;

        return $this;
    }

    public function setFactory(?ServiceConfig $factory)
    {
        $this->factory = $factory;

        return $this;
    }

    public function getFactory(): ?ServiceConfig
    {
        return $this->factory;
    }

    public function setRepository(?ServiceConfig $repostory)
    {
        $this->repository = $repostory;

        return $this;
    }

    public function getRepository(): ?ServiceConfig
    {
        return $this->repository;
    }

    public function setNormalizationContext(?SerializationConfig $normalizationContext)
    {
        $this->normalizationContext = $normalizationContext;

        return $this;
    }

    public function getNormalizationContext(): ?SerializationConfig
    {
        return $this->normalizationContext;
    }

    public function setDenormalizationContext(?SerializationConfig $denormalizationContext)
    {
        $this->denormalizationContext = $denormalizationContext;

        return $this;
    }

    public function getDenormalizationContext(): ?SerializationConfig
    {
        return $this->denormalizationContext;
    }

     /**
     * Checks whether the configuration of at least one operation exists.
     *
     * @return bool
     */
    public function hasOperations()
    {
        return !empty($this->operations);
    }

    /**
     * Gets the configuration for all operations.
     *
     * @return OperationConfig[] [operation name => config, ...]
     */
    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * Checks whether the configuration of the operation exists.
     *
     * @param string $operationName
     *
     * @return bool
     */
    public function hasOperation($operationName)
    {
        return isset($this->operations[$operationName]);
    }

    /**
     * Gets the configuration of the operation.
     *
     * @param string $operationName
     *
     * @return OperationConfig|null
     */
    public function getOperation($operationName)
    {
        if (!isset($this->operations[$operationName])) {
            return null;
        }

        return $this->operations[$operationName];
    }

    public function getOperationAttribute(string $operationName, string $key)
    {
        $operationAttribute = null;

        if ($this->hasOperation($operationName)) {
            $operation = $this->getOperation($operationName);
            if ($operation && $getter = $this->getGetter($operation, $key)) {
                $operationAttribute = $operation->$getter();
            }
        }

        $resourceAttribute = null;
        if ($getter = $this->getGetter($this, $key)) {
            $resourceAttribute = $this->$getter();
        }

        if (empty($operationAttribute)) {
            return $resourceAttribute;
        }

        if ($operationAttribute && $operationAttribute instanceof ServiceConfig) {
            return ServiceConfig::fromArray(array_merge($resourceAttribute->toArray(), array_filter($operationAttribute->toArray())));
        }

        if ($operationAttribute && $operationAttribute instanceof SerializationConfig) {
            return SerializationConfig::fromArray(array_merge($resourceAttribute->toArray(), array_filter($operationAttribute->toArray())));
        }

        if (!empty($operationAttribute)) {
            return $operationAttribute;
        }

        return null;
    }

    /**
     * Adds the configuration of the operation.
     *
     * @param string                 $operationName
     * @param OperationConfig|null $operation
     *
     * @return OperationConfig
     */
    public function addOperation($operationName, $operation = null)
    {
        if (null === $operation) {
            $operation = new OperationConfig();
        }

        $this->operations[$operationName] = $operation;

        return $operation;
    }

    /**
     * Removes the configuration of the operation.
     *
     * @param string $operationName
     */
    public function removeOperation($operationName)
    {
        unset($this->operations[$operationName]);
    }

    /**
     * @return mixed
     */
    public function getValidationGroups()
    {
        return $this->validationGroups;
    }

    /**
     * @param mixed $validationGroups
     *
     * @return self
     */
    public function setValidationGroups($validationGroups)
    {
        $this->validationGroups = $validationGroups;

        return $this;
    }

     /**
     * @param object $config
     * @param string $key
     *
     * @return string|string[]|null
     */
    protected function getGetter($config, $key)
    {
        $setter = 'get' . $this->camelize($key);

        return \method_exists($config, $setter)
            ? $setter
            : null;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected function camelize($string)
    {
        return strtr(\ucwords(strtr($string, ['_' => ' '])), [' ' => '']);
    }
}
