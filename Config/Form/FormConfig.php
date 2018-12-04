<?php

namespace Videni\Bundle\RestBundle\Config\Form;

class FormConfig
{
    private $formType;

    private $formOptions;

    private $fields;

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
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param mixed $fields
     *
     * @return self
     */
    public function setFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    public static function fromArray($config)
    {
        $self = new self();

        if (array_key_exists('form_type', $config)) {
            $self->setFormType($config['form_type']);
        }
        if (array_key_exists('form_options', $config)) {
            $self->setDescription($config['form_options']);
        }
        if (array_key_exists('fields', $config)) {
            $self->setFields($config['fields']);
        }

        return $self;
    }
}
