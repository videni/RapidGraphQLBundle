<?php

namespace Videni\Bundle\RestBundle\Processor\CustomizeFormData;

use Doctrine\Common\Collections\Collection;
use Videni\Bundle\RestBundle\Config\Form\FormFieldConfig;
use Videni\Bundle\RestBundle\Processor\FormContext;
use Videni\Bundle\RestBundle\Util\ConfigUtil;
use Oro\Component\ChainProcessor\ActionProcessorInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

/**
 * Provides functionality to handle all types of form related events dispatched in "customize_form_data" action.
 */
class CustomizeFormDataHandler
{
    public const API_CONTEXT       = 'api_context';
    public const API_EVENT_CONTEXT = 'api_event_context';

    /** @var ActionProcessorInterface */
    private $customizationProcessor;

    /**
     * @param ActionProcessorInterface $customizationProcessor
     */
    public function __construct(ActionProcessorInterface $customizationProcessor)
    {
        $this->customizationProcessor = $customizationProcessor;
    }

    /**
     * @param string    $eventName
     * @param FormEvent $event
     *
     * @return CustomizeFormDataContext|null
     */
    public function handleFormEvent(string $eventName, FormEvent $event): ?CustomizeFormDataContext
    {
        $context = $this->getInitializedContext($event->getForm());
        if (null !== $context) {
            $context->setEvent($eventName);
            $context->setData($event->getData());
            $this->customizationProcessor->process($context);
        }

        return $context;
    }

    /**
     * @param FormInterface $form
     *
     * @return CustomizeFormDataContext|null
     */
    private function getInitializedContext(FormInterface $form): ?CustomizeFormDataContext
    {
        /** @var CustomizeFormDataContext $context */
        $context = $form->getConfig()->getAttribute(self::API_EVENT_CONTEXT);
        if ($context->isInitialized()) {
            // already initialized
            return $context;
        }

        $rootFormConfig = $form->getConfig();
        if (!$rootFormConfig->hasAttribute(self::API_CONTEXT)) {
            // by some reasons the root form does not have the context of API action
            return null;
        }

        /** @var FormContext $formContext */
        $formContext = $rootFormConfig->getAttribute(self::API_CONTEXT);
        $context->setVersion($formContext->getVersion());
        $context->setClassName($form->getConfig()->getDataClass());
        $context->setParentAction($formContext->getAction());
        $context->setForm($form);
        $config = $formContext->getResourceConfig();
        if (null === $form->getParent()) {
            $context->setConfig($config);
        } else {
            $context->setRootClassName($rootFormConfig->getDataClass());
            $context->setPropertyPath($propertyPath);
            $context->setRootConfig($config);
        }

        return $context;
    }
}
