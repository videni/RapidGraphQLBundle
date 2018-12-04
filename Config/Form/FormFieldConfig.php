<?php

namespace Videni\Bundle\RestBundle\Config\Form;

use Videni\Bundle\RestBundle\Config\Resource\ResourceConfig;

class FormFieldConfig
{
    private $exclude;
    private $description;
    private $propertyPath;
    private $dataType;
    private $targetClass;
    private $targetType;
    private $collapse;
    private $formType;
    private $formOptions;
    private $dependsOn;
    private $dataTransformer;
    private $targetResource;
    private $exclusionPolicy;
    private $formEventSubscribers = [];
    private $fields = [];

    /**
     * @return mixed
     */
    public function getExclude()
    {
        return $this->exclude;
    }

    /**
     * @param mixed $exclude
     *
     * @return self
     */
    public function setExclude($exclude)
    {
        $this->exclude = $exclude;

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
    public function getPropertyPath()
    {
        return $this->propertyPath;
    }

    /**
     * @param mixed $propertyPath
     *
     * @return self
     */
    public function setPropertyPath($propertyPath)
    {
        $this->propertyPath = $propertyPath;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param mixed $dataType
     *
     * @return self
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTargetClass()
    {
        return $this->targetClass;
    }

    /**
     * @param mixed $targetClass
     *
     * @return self
     */
    public function setTargetClass($targetClass)
    {
        $this->targetClass = $targetClass;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTargetType()
    {
        return $this->targetType;
    }

    /**
     * @param mixed $targetType
     *
     * @return self
     */
    public function setTargetType($targetType)
    {
        $this->targetType = $targetType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCollapse()
    {
        return $this->collapse;
    }

    /**
     * @param mixed $collapse
     *
     * @return self
     */
    public function setCollapse($collapse)
    {
        $this->collapse = $collapse;

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
    public function getDependsOn()
    {
        return $this->dependsOn;
    }

    /**
     * @param mixed $dependsOn
     *
     * @return self
     */
    public function setDependsOn($dependsOn)
    {
        $this->dependsOn = $dependsOn;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDataTransformer()
    {
        return $this->dataTransformer;
    }

    /**
     * @param mixed $dataTransformer
     *
     * @return self
     */
    public function setDataTransformer($dataTransformer)
    {
        $this->dataTransformer = $dataTransformer;

        return $this;
    }

  /**
     * Indicates whether the target resource configuration exists.
     * This configuration makes sense only if the field represents an association with another resource.
     *
     * @return bool
     */
    public function hasTargetResource()
    {
        return null !== $this->getTargetResource();
    }

    /**
     * Gets the configuration of the target resource.
     * If the configuration does not exist it is created automatically.
     * Use this method only if the field represents an association with another resource.
     *
     * @return ResourceConfig
     */
    public function getOrCreateTargetResource()
    {
        $targetResource = $this->getTargetResource();
        if (null === $targetResource) {
            $targetResource = $this->createAndSetTargetResource();
        }

        return $targetResource;
    }

    /**
     * Creates new instance of the target resource.
     * If the field already have the configuration of the target resource it will be overridden.
     * Use this method only if the field represents an association with another resource.
     *
     * @return ResourceConfig
     */
    public function createAndSetTargetResource()
    {
        return $this->setTargetResource(new ResourceConfig());
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
     * Gets the form event subscribers.
     *
     * @return string[]|null Each element in the array is the name of a service implements EventSubscriberInterface
     */
    public function getFormEventSubscribers()
    {
        return $this->formEventSubscribers;
    }

    /**
     * Sets the form event subscribers.
     *
     * @param string[]|null $eventSubscribers Each element in the array should be
     *                                        the name of a service implements EventSubscriberInterface
     */
    public function setFormEventSubscribers(array $eventSubscribers)
    {
        $this->formEventSubscribers = $eventSubscribers;
    }

    /**
     * Adds the form event subscriber.
     *
     * @param string $eventSubscriber The name of a service implements EventSubscriberInterface
     */
    public function addFormEventSubscriber($eventSubscriber)
    {
       $this->formEventSubscribers[] = $eventSubscriber;
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
     * @return FormFieldConfig[] [field name => config, ...]
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
     * @return FormFieldConfig|null
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
     * @param FormFieldConfig|null $field
     *
     * @return FormFieldConfig
     */
    public function addField($fieldName, $field = null)
    {
        if (null === $field) {
            $field = new FormFieldConfig();
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
}
