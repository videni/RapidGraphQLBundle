<?php

namespace Videni\Bundle\RestBundle\Form;

interface FormExtensionCheckerInterface
{
    /**
     * Checks whether Data API form extension is activated.
     */
    public function isFormExtensionActivated();
}
