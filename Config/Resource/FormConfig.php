<?php

namespace Videni\Bundle\RapidGraphQLBundle\Config\Resource;

class FormConfig
{
    private $form = [];

     /**
     * @return mixed
     */
    public function getFormClass()
    {
        return isset($this->form['class'])?  $this->form['class']: null;
    }

    /**
     * @param mixed $form
     *
     * @return self
     */
    public function setFormClass($form)
    {
        $this->form['class'] = $form;

        return $this;
    }

     /**
     * @param mixed $validationGroups
     *
     * @return self
     */
    public function setFormValidationGroups($validationGroups)
    {
        $this->form['validation_groups'] = $validationGroups;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormValidationGroups()
    {
        return isset($this->form['validation_groups']) ? $this->form['validation_groups']: null;
    }

     /**
     * @param mixed $validationGroups
     *
     * @return self
     */
    public function setFormHandler($onSuccess)
    {
        $this->form['handler'] = $onSuccess;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormHandler()
    {
        return isset($this->form['handler']) ? $this->form['handler']: null;
    }

    public static function create(FormConfig $self, array $config)
    {
        if (array_key_exists('form', $config)) {
            if(isset($config['form']['class'])) {
                $self->setFormClass($config['form']['class']) ;
            }
            if(isset($config['form']['validation_groups'])) {
                $self->setFormValidationGroups($config['form']['validation_groups']);
            }
            if(isset($config['form']['handler'])) {
                $self->setFormHandler($config['form']['handler']);
            }
        }
    }
}
