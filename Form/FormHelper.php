<?php

namespace Videni\Bundle\RestBundle\Form;

use Videni\Bundle\RestBundle\Config\Form\FormFieldConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Provides a set of reusable utility methods to simplify
 * creation and configuration of FormBuilder for forms used in Data API actions,
 * such as "create", "update"
 */
class FormHelper
{
    public const EXTRA_FIELDS_MESSAGE = 'videni.api.form.extra_fields';

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var PropertyAccessorInterface */
    private $propertyAccessor;

    /** @var ContainerInterface */
    private $container;

    /**
     * @param FormFactoryInterface      $formFactory
     * @param PropertyAccessorInterface $propertyAccessor
     * @param ContainerInterface        $container
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        PropertyAccessorInterface $propertyAccessor,
        ContainerInterface $container
    ) {
        $this->formFactory = $formFactory;
        $this->propertyAccessor = $propertyAccessor;
        $this->container = $container;
    }

    /**
     * Creates a form builder.
     * Please note that the form validation is disabled by default,
     * to enable it use "enable_validation" option.
     * @see getFormDefaultOptions to find all default options
     *
     * @param string     $formType
     * @param mixed      $data
     * @param array      $options
     * @param array|null $eventSubscribers
     *
     * @return FormBuilderInterface
     */
    public function createFormBuilder($formType, $data, array $options, array $eventSubscribers = null)
    {
        $formBuilder = $this->formFactory->createNamedBuilder(
            null,
            $formType,
            $data,
            \array_merge($this->getFormDefaultOptions(), $options)
        );
        $formBuilder->setDataMapper(new PropertyPathMapper($this->propertyAccessor));
        if (!empty($eventSubscribers)) {
            $this->addFormEventSubscribers($formBuilder, $eventSubscribers);
        }

        return $formBuilder;
    }

    /**
     * Adds all entity fields to the given form.
     *
     * @param FormBuilderInterface   $formBuilder
     * @param FormFieldConfig $formConfig
     */
    public function addFormFields(
        FormBuilderInterface $formBuilder,
        FormFieldConfig $formConfig
    ) {
        $fields = $formConfig->getFields();
        foreach ($fields as $name => $field) {
            $this->addFormField($formBuilder, $name, $field);
        }
    }

    /**
     * Adds a field to the given form.
     *
     * @param FormBuilderInterface        $formBuilder
     * @param string                      $fieldName
     * @param FormFieldConfig $fieldConfig
     * @param array                       $options
     *
     * @return FormBuilderInterface
     */
    public function addFormField(
        FormBuilderInterface $formBuilder,
        $fieldName,
        FormFieldConfig $fieldConfig,
        array $options = []
    ) {
        $fieldFormBuilder = $formBuilder->add(
            $fieldName,
            $fieldConfig->getFormType(),
            \array_replace($options, $fieldConfig->getFormOptions())
        );

        $targetConfig = $fieldConfig->getTargetEntity();
        if (null !== $targetConfig) {
            $eventSubscribers = $targetConfig->getFormEventSubscribers();
            if (!empty($eventSubscribers)) {
                $this->addFormEventSubscribers($fieldFormBuilder, $eventSubscribers);
            }
        }

        return $fieldFormBuilder;
    }

    /**
     * Returns default options of a form.
     *
     * @return array
     */
    private function getFormDefaultOptions()
    {
        return [
            'validation_groups'    => ['Default', 'api'],
            'extra_fields_message' => self::EXTRA_FIELDS_MESSAGE,
            'enable_validation'    => false
        ];
    }

    /**
     * @param FormBuilderInterface $formBuilder
     * @param array                $eventSubscribers
     */
    private function addFormEventSubscribers(FormBuilderInterface $formBuilder, array $eventSubscribers)
    {
        foreach ($eventSubscribers as $eventSubscriber) {
            if (\is_string($eventSubscriber)) {
                $eventSubscriber = $this->container->get($eventSubscriber);
            }
            $formBuilder->addEventSubscriber($eventSubscriber);
        }
    }
}
