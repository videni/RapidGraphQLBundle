<?php

namespace Videni\Bundle\RestBundle\Config\Form;

use Videni\Bundle\RestBundle\Exception\ConfigNotFoundException;

class FormConfigProvider
{
   /**
     * @var FormConfig[]
     */
    private $formConfigs = [];

    private $formConfigurations;
    private $formConfigLoader;

    public function __construct(
        FormConfigLoader $formConfigLoader,
        array $formConfigurations
    ) {
        $this->formConfigLoader = $formConfigLoader;
        $this->formConfigurations = $formConfigurations;
    }

    public function get($formClass, $formName)
    {
        if (array_key_exists($formClass, $this->formConfigs) && isset($this->formConfigs[$formClass][$formName])) {
            return  $this->formConfigs[$formClass][$formName];
        }

        if (isset($this->formConfigurations['forms'][$formClass])) {
            foreach ($$this->formConfigurations['forms'][$formClass] as $formName => $formConfig) {
                $formConfig  = $this->formConfigLoader->load($formConfig);

                $this->formConfigs[$formClass][$formName] = $formConfig;
            }

            return $formConfig;
        }

        throw new ConfigNotFoundException('Form', $formClass);
    }

    public function getAll()
    {
        foreach ($this->formConfigurations['forms'] as $formClass => $formConfiguration) {
            foreach ($formConfiguration as $formName => $formConfig) {
                $formConfig  = $this->formConfigLoader->load($formConfig);

                $this->formConfigs[$formClass][$formName] = $formConfig;
            }
        }

        return $this->formConfigs;
    }
}
