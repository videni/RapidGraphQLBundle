<?php

namespace Videni\Bundle\RestBundle\Config\Entity;

class FormConfig
{
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

    private $exclusionPolicy;
    private $postSerialize;
    private $formType;
    private $formOptions;
    private $formEventSubscriber;
    private $parentResourceClass;

    private $fields = [];

    /**
     * Gets a string that unique identify this instance of entity definition config.
     *
     * @return string|null
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Sets a string that unique identify this instance of entity definition config.
     * Do not set this value in your code.
     *
     * @param string|null $key
     */
    public function setKey($key)
    {
        $this->key = $key;
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
    public function getExclusionPolicy()
    {
        return $this->exclusionPolicy;
    }

    /**
     * @param mixed $exclusionPolicy
     *
     * @return self
     */
    public function setExclusionPolicy($exclusionPolicy)
    {
        $this->exclusionPolicy = $exclusionPolicy;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostSerialize()
    {
        return $this->postSerialize;
    }

    /**
     * @param mixed $postSerialize
     *
     * @return self
     */
    public function setPostSerialize($postSerialize)
    {
        $this->postSerialize = $postSerialize;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormType()
    {
        return $this->formType;
    }

    /**
     * @param mixed $formType
     *
     * @return self
     */
    public function setFormType($formType)
    {
        $this->formType = $formType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormOptions()
    {
        return $this->formOptions;
    }

    /**
     * @param mixed $formOptions
     *
     * @return self
     */
    public function setFormOptions($formOptions)
    {
        $this->formOptions = $formOptions;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormEventSubscriber()
    {
        return $this->formEventSubscriber;
    }

    /**
     * @param mixed $formEventSubscriber
     *
     * @return self
     */
    public function setFormEventSubscriber($formEventSubscriber)
    {
        $this->formEventSubscriber = $formEventSubscriber;

        return $this;
    }

     /**
     * Checks whether the configuration of at least one field exists.
     *
     * @return bool
     */
    public function hasFields()
    {
        return !empty($this->fields);
    }

    /**
     * Gets the configuration for all fields.
     *
     * @return FieldConfig[] [field name => config, ...]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Checks whether the configuration of the field exists.
     *
     * @param string $fieldName
     *
     * @return bool
     */
    public function hasField($fieldName)
    {
        return isset($this->fields[$fieldName]);
    }

    /**
     * Gets the configuration of the field.
     *
     * @param string $fieldName
     *
     * @return FieldConfig|null
     */
    public function getField($fieldName)
    {
        if (!isset($this->fields[$fieldName])) {
            return null;
        }

        return $this->fields[$fieldName];
    }

    /**
     * Adds the configuration of the field.
     *
     * @param string                 $fieldName
     * @param FieldConfig|null $field
     *
     * @return FieldConfig
     */
    public function addField($fieldName, $field = null)
    {
        if (null === $field) {
            $field = new FieldConfig();
        }

        $this->fields[$fieldName] = $field;

        return $field;
    }

    /**
     * Removes the configuration of the field.
     *
     * @param string $fieldName
     */
    public function removeField($fieldName)
    {
        unset($this->fields[$fieldName]);
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
}
