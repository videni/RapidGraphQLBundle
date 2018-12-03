<?php

namespace Videni\Bundle\RestBundle\Config\Entity;

class FieldConfig
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

    public static function fromArray($config)
    {
        $self = new self();

        if (array_key_exists('exclude', $config)) {
            $self->setType($config['exclude']);
        }
        if (array_key_exists('description', $config)) {
            $self->setDescription($config['description']);
        }
        if (array_key_exists('propertyPath', $config)) {
            $self->setAllowArray($config['propertyPath']);
        }
        if (array_key_exists('dataType', $config)) {
            $self->setAllowRange($config['dataType']);
        }
        if (array_key_exists('targetClass', $config)) {
            $self->setPropertyPath($config['targetClass']);
        }
        if (array_key_exists('targetType', $config)) {
            $self->setCollection($config['targetType']);
        }
        if (array_key_exists('collapse', $config)) {
            $self->setPosition($config['collapse']);
        }
        if (array_key_exists('formType', $config)) {
            $self->setOptions($config['formType']);
        }
        if (array_key_exists('formOptions', $config)) {
            $self->setOperators($config['formOptions']);
        }
        if (array_key_exists('dependsOn', $config)) {
            $self->setOperators($config['dependsOn']);
        }
        if (array_key_exists('dataTransformer', $config)) {
            $self->setOperators($config['dataTransformer']);
        }

        return $self;
    }
}
