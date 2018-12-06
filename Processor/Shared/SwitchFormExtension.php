<?php

namespace Videni\Bundle\RestBundle\Processor\Shared;

use Videni\Bundle\RestBundle\Form\FormExtensionSwitcherInterface;
use Videni\Bundle\RestBundle\Processor\FormContext;

abstract class SwitchFormExtension
{
    const API_FORM_EXTENSION_ACTIVATED = 'apiFormExtensionActivated';

    const PREVIOUS_ENTITY_MAPPER      = 'previousEntityMapper';

    /** @var FormExtensionSwitcherInterface */
    protected $formExtensionSwitcher;

    /**
     * @param FormExtensionSwitcherInterface $formExtensionSwitcher
     */
    public function __construct(
        FormExtensionSwitcherInterface $formExtensionSwitcher
    ) {
        $this->formExtensionSwitcher = $formExtensionSwitcher;
    }

    /**
     * @param FormContext $context
     *
     * @return bool
     */
    protected function isApiFormExtensionActivated(FormContext $context)
    {
        return (bool)$context->get(self::API_FORM_EXTENSION_ACTIVATED);
    }

    /**
     * @param FormContext $context
     */
    protected function switchToApiFormExtension(FormContext $context)
    {
        $this->formExtensionSwitcher->switchToApiFormExtension();
        $context->set(self::API_FORM_EXTENSION_ACTIVATED, true);
    }

    /**
     * @param FormContext $context
     */
    protected function switchToDefaultFormExtension(FormContext $context)
    {
        $this->formExtensionSwitcher->switchToDefaultFormExtension();
        $context->remove(self::API_FORM_EXTENSION_ACTIVATED);
    }
}
