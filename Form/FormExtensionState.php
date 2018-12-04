<?php

namespace Videni\Bundle\RestBundle\Form;

class FormExtensionState implements FormExtensionCheckerInterface
{
    /** @var bool */
    private $isFormExtensionActivated = false;

    /**
     * {@inheritdoc}
     */
    public function isFormExtensionActivated()
    {
        return $this->isFormExtensionActivated;
    }

    /**
     * Switches to default form extension.
     */
    public function switchToDefaultFormExtension()
    {
        $this->isFormExtensionActivated = false;
    }

    /**
     * Switches to Data API form extension.
     */
    public function switchToApiFormExtension()
    {
        $this->isFormExtensionActivated = true;
    }
}
