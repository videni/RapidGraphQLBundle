<?php

namespace Videni\Bundle\RestBundle\Processor;

use Doctrine\Common\Util\ClassUtils;
use Videni\Bundle\RestBundle\Config\Entity\FormConfig;
use Videni\Bundle\RestBundle\Form\Extension\ValidationExtension;
use Videni\Bundle\RestBundle\Form\FormHelper;
use Videni\Bundle\RestBundle\Processor\CustomizeFormData\CustomizeFormDataHandler;
use Videni\Bundle\RestBundle\Processor\FormContext;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Builds the form builder based on the entity metadata and configuration
 * and sets it to the context.
 */
class BuildFormBuilder implements ProcessorInterface
{
    /** @var FormHelper */
    protected $formHelper;

    protected $formConfigProvider;

    /** @var bool */
    protected $enableFullValidation;

    /**
     * @param FormHelper $formHelper
     * @param bool       $enableFullValidation
     */
    public function __construct(FormHelper $formHelper, FormConfigProvider $formConfigProvider, bool $enableFullValidation = false)
    {
        $this->formHelper = $formHelper;
        $this->formConfigProvider = $formConfigProvider;
        $this->enableFullValidation = $enableFullValidation;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var FormContext $context */

        if ($context->hasFormBuilder()) {
            // the form builder is already built
            return;
        }
        if ($context->hasForm()) {
            // the form is already built
            return;
        }

        if (!$context->hasResult()) {
            // the entity is not defined
            throw new \RuntimeException(
                'The entity object must be added to the context before creation of the form builder.'
            );
        }

        $formBuilder = $this->getFormBuilder($context);
        if (null !== $formBuilder) {
            $context->setFormBuilder($formBuilder);
        }
    }

    protected function loadFormConfig(ContextInterface$context)
    {
        $resourceConfig = $context->getResourceConfig();

        $formName = $resourceConfig->getOperationAttribute($context->getOperationName(), 'form');

        return $this->formConfigProvider->get($context->getClassName(), $formName);
    }

    /**
     * @param FormContext $context
     *
     * @return FormBuilderInterface|null
     */
    protected function getFormBuilder(FormContext $context)
    {
        $formConfig = $this->loadFormConfig($context);
        if (null === $formConfig) {
            return null;
        }

        $formType = $formConfig->getFormType() ?: FormType::class;

        $formBuilder = $this->formHelper->createFormBuilder(
            $formType,
            $context->getResult(),
            $this->getFormOptions($context, $formConfig),
            $formConfig->getFormEventSubscribers()
        );

        if (FormType::class === $formType) {
            if (null !== $metadata) {
                $this->formHelper->addFormFields($formBuilder, $formConfig);
            }
        }

        return $formBuilder;
    }

    /**
     * @param FormContext            $context
     * @param FormConfig $config
     *
     * @return array
     */
    protected function getFormOptions(FormContext $context, FormConfig $config)
    {
        $options = $config->getFormOptions();
        if (null === $options) {
            $options = [];
        }
        if (!\array_key_exists('data_class', $options)) {
            $options['data_class'] = $this->getFormDataClass($context, $config);
        }
        $options[CustomizeFormDataHandler::API_CONTEXT] = $context;
        $options[ValidationExtension::ENABLE_FULL_VALIDATION] = $this->enableFullValidation;

        return $options;
    }

    /**
     * @param FormContext            $context
     * @param FormConfig $config
     *
     * @return string
     */
    protected function getFormDataClass(FormContext $context, FormConfig $config)
    {
        $dataClass = $context->getClassName();
        $entity = $context->getResult();
        if (\is_object($entity)) {
            $parentResourceClass = $config->getParentResourceClass();
            if ($parentResourceClass) {
                $entityClass = ClassUtils::getClass($entity);
                if ($entityClass !== $dataClass && $entityClass === $parentResourceClass) {
                    $dataClass = $parentResourceClass;
                }
            }
        }

        return $dataClass;
    }
}
