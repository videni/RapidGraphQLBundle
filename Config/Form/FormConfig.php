<?php

namespace Videni\Bundle\RestBundle\Config\Form;

class FormConfig
{
    private $formType;

    private $formOptions;

    private $fields;

    private $formEventSubscribers = [];

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
            $self->setFormOptions($config['form_options']);
        }
        if (array_key_exists('fields', $config)) {
            $self->setFields($config['fields']);
        }
        if (array_key_exists('form_event_subscribers', $config)) {
            $self->setFormEventSubscribers($config['form_event_subscribers']);
        }

        return $self;
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
}
