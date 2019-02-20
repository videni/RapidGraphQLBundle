<?php

namespace Videni\Bundle\RestBundle\Config\Resource;

use Videni\Bundle\RestBundle\Config\Grid\Grid;
use Videni\Bundle\RestBundle\Config\Form\FormFieldConfig;

class Resource
{
    private $routePrefix;
    private $scope;
    private $shortName;
    private $description;

    private $formats = null;
    private $factoryClass = null;
    private $repositoryClass = null;
    private $normalizationContext = null;
    private $denormalizationContext = null;
    private $validationGroups = null;
    private $form = null;

    private $operations = [];
    private $grids = [];

     /**
     * A string that unique identify this instance of entity definition config.
     * This value is set by config providers and is used by a metadata provider
     * to build a metadata cache key. It allows to avoid loading the same metadata
     * several times and as result it improves a performance.
     *
     * @var string|null
     */
    private $key;

    /** @var string[] */
    private $identifierFieldNames = [];

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

    public function setNormalizationContext(?Serialization $normalizationContext)
    {
        $this->normalizationContext = $normalizationContext;

        return $this;
    }

    public function getNormalizationContext(): ?Serialization
    {
        return $this->normalizationContext;
    }

    public function setDenormalizationContext(?Serialization $denormalizationContext)
    {
        $this->denormalizationContext = $denormalizationContext;

        return $this;
    }

    public function getDenormalizationContext(): ?Serialization
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
     * @return Operation[] [operation name => config, ...]
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
     * @return Operation|null
     */
    public function getOperation($operationName)
    {
        if (!isset($this->operations[$operationName])) {
            return null;
        }

        return $this->operations[$operationName];
    }

     /**
     * Adds the configuration of the operation.
     *
     * @param string                 $operationName
     * @param Operation|null $operation
     *
     * @return Operation
     */
    public function addOperation($operationName, $operation = null)
    {
        if (null === $operation) {
            $operation = new Operation();
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

    public function getOperationAttribute(string $operationName, string $key, $fallback = false)
    {
        $operationAttribute = null;

        if ($this->hasOperation($operationName)) {
            $operation = $this->getOperation($operationName);
            if ($operation && $getter = $this->getGetter($operation, $key)) {
                $operationAttribute = $operation->$getter();
            }
        }

        if($operationAttribute instanceof Serialization) {
            $mergedAttribtues = $this->mergeResourceLevelAttributes($key, $operationAttribute);
            if($mergedAttribtues !== false) {
                return Serialization::fromArray($mergedAttribtues);
            }
        };

        //empty array
        if (is_array($operationAttribute) && empty($operationAttribute)) {
            $operationAttribute = null;
        }
        if (null !== $operationAttribute) {
            return $operationAttribute;
        }

        if($fallback) {
            return $this->getResourceLevelAttribute($key);
        }

        return null;
    }

    /**
     * Checks whether the configuration of at least one operation exists.
     *
     * @return bool
     */
    public function hasGrids()
    {
        return !empty($this->grids);
    }

    /**
     * Gets the configuration for all grids.
     *
     * @return Grid[] [grid name => config, ...]
     */
    public function getGrids()
    {
        return $this->grids;
    }

    /**
     * Checks whether the configuration of the grid exists.
     *
     * @param string $gridName
     *
     * @return bool
     */
    public function hasGrid($gridName)
    {
        return isset($this->grids[$gridName]);
    }

    /**
     * Gets the configuration of the grid.
     *
     * @param string $gridName
     *
     * @return Grid|null
     */
    public function getGrid($gridName)
    {
        if (!isset($this->grids[$gridName])) {
            return null;
        }

        return $this->grids[$gridName];
    }

     /**
     * Adds the configuration of the grid.
     *
     * @param string                 $gridName
     * @param Grid|null $grid
     *
     * @return Grid
     */
    public function addGrid($gridName, $grid = null)
    {
        if (null === $grid) {
            $grid = new Grid();
        }

        $this->grids[$gridName] = $grid;

        return $grid;
    }

    /**
     * Removes the configuration of the grid.
     *
     * @param string $gridName
     */
    public function removeGrid($gridName)
    {
        unset($this->grids[$gridName]);
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
     * @return mixed
     */
    public function getValidationGroups()
    {
        return $this->validationGroups;
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

    /**
     * Gets the names of identifier fields of the entity.
     *
     * @return string[]
     */
    public function getIdentifierFieldNames()
    {
        return $this->identifierFieldNames;
    }

    /**
     * Sets the names of identifier fields of the entity.
     *
     * @param string[] $fields
     */
    public function setIdentifierFieldNames(array $fields)
    {
        $this->identifierFieldNames = $fields;
    }

      /**
     * @return mixed
     */
    public function getParentResourceClass()
    {
        return $this->parentResourceClass;
    }

    /**
     * @param mixed $parentResourceClass
     *
     * @return self
     */
    public function setParentResourceClass($parentResourceClass)
    {
        $this->parentResourceClass = $parentResourceClass;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param mixed $form
     *
     * @return self
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    private function mergeResourceLevelAttributes($key, $operationLevelAttribute)
    {
        $resourceLevelAttribute  = $this->getResourceLevelAttribute($key);
        if(null !== $resourceLevelAttribute) {
            return array_merge(
                    $resourceLevelAttribute->toArray(),
                    array_filter($operationLevelAttribute->toArray(),
                    function($value) {
                        return $value !== null;
                    }
                )
            );
        }

        return false;
    }

    private function getResourceLevelAttribute($key)
    {
        $resourceLevelAttribute = null;
        if ($getter = $this->getGetter($this, $key)){
            $resourceLevelAttribute = $this->$getter();
        }

        return $resourceLevelAttribute;
    }

    /**
     * @return mixed
     */
    public function getRepositoryClass()
    {
        return $this->repositoryClass;
    }

    /**
     * @param mixed $repositoryClass
     *
     * @return self
     */
    public function setRepositoryClass($repositoryClass)
    {
        $this->repositoryClass = $repositoryClass;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFactoryClass()
    {
        return $this->factoryClass;
    }

    /**
     * @param mixed $factoryClass
     *
     * @return self
     */
    public function setFactoryClass($factoryClass)
    {
        $this->factoryClass = $factoryClass;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param mixed $scope
     *
     * @return self
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }
}
