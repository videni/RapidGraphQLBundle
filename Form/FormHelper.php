<?php

namespace Videni\Bundle\RestBundle\Form;

use Videni\Bundle\RestBundle\Config\Form\FormFieldConfig;
use Videni\Bundle\RestBundle\Config\Resource\ResourceConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Videni\Bundle\RestBundle\Model\DataType;
use Videni\Bundle\RestBundle\Form\Type\CollectionType;
use Videni\Bundle\RestBundle\Form\Type\CompoundObjectType;
use Videni\Bundle\RestBundle\Form\Type\EntityCollectionType;
use Videni\Bundle\RestBundle\Form\Type\EntityScalarCollectionType;
use Videni\Bundle\RestBundle\Form\Type\EntityType;
use Videni\Bundle\RestBundle\Form\Type\NestedAssociationType;
use Videni\Bundle\RestBundle\Form\Type\ScalarCollectionType;
use Videni\Bundle\RestBundle\Util\DoctrineHelper;

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

    /** @var array [data type => [form type, options], ...] */
    protected $dataTypeMappings = [];

    protected $doctrineHelper;

    /**
     * @param FormFactoryInterface      $formFactory
     * @param PropertyAccessorInterface $propertyAccessor
     * @param ContainerInterface        $container
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        PropertyAccessorInterface $propertyAccessor,
        ContainerInterface $container,
        DoctrineHelper $doctrineHelper,
        array $dataTypeMappings
    ) {
        $this->formFactory = $formFactory;
        $this->propertyAccessor = $propertyAccessor;
        $this->container = $container;
        $this->dataTypeMappings = $dataTypeMappings;
        $this->doctrineHelper = $doctrineHelper;
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
        FormFieldConfig $fieldConfig
    ) {
        $fields = $fieldConfig->getFields();
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
     *
     * @return FormBuilderInterface
     */
    public function addFormField(
        FormBuilderInterface $formBuilder,
        $fieldName,
        FormFieldConfig $fieldConfig
    ) {
        $formType = $fieldConfig->getFormType() ?? null;
        $options = [];

        if (!$formType && $dataType = $fieldConfig->getDataType()) {
            if (isset($this->dataTypeMappings[$dataType])) {
                list($formType, $options) = $this->dataTypeMappings[$dataType];
            }
            if (DataType::isNestedObject($dataType)) {
                $formType = CompoundObjectType::class;

                $options = array_merge(
                    $fieldConfig->getFormOptions(),
                    [
                        'field_config'   => $fieldConfig
                    ]
                );
            }
            if (DataType::isCollectionAssociation($dataType)) {
                list($formType, $options) = !$fieldConfig->isCollapsed() ? $this->getTypeForCollectionAssociation($fieldConfig): $this->getTypeForCollapsedCollectionAssociation($fieldConfig);
            }
        }

        $fieldFormBuilder = $formBuilder->add(
            $fieldName,
            $formType,
            $options
        );

        $dataTransformer = $fieldConfig->getDataTransformer();
        if ($dataTransformer) {
            $dataTransformer = $this->container->get($dataTransformer);
            $fieldFormBuilder->addModelTransfomer($dataTransformer);
        }

        $eventSubscribers = $fieldConfig->getFormEventSubscribers();
        if (!empty($eventSubscribers)) {
            $this->addFormEventSubscribers($fieldFormBuilder, $eventSubscribers);
        }

        return $fieldFormBuilder;
    }

     /**
     * @param FormFieldConfig   $fieldConfig
     *
     * @return []
     */
    protected function getTypeForCollectionAssociation(
        FormFieldConfig $fieldConfig
    ) {
        $targetClass = $fieldConfig->getTargetClass();

        $formType = $this->doctrineHelper->isManageableEntityClass($targetClass)
            ? EntityCollectionType::class
            : CollectionType::class;

        return
            [
                $formType,
                [
                    'entry_data_class' => $targetClass,
                    'entry_type'       => CompoundObjectType::class,
                    'entry_options'    => [
                        'field_config'   => $fieldConfig
                    ]
                ]
            ];
    }

    /**
     * @param FormFieldConfig $fieldConfig
     *
     * @return TypeGuess|null
     */
    protected function getTypeForCollapsedCollectionAssociation(FormFieldConfig $fieldConfig)
    {
        $formType = $this->doctrineHelper->isManageableEntityClass($fieldConfig->getTargetClass())
            ? EntityScalarCollectionType::class
            : ScalarCollectionType::class;

        return [
            $formType,
            [
                'entry_data_class'    => $targetMetadata->getClassName(),
                'entry_data_property' => $targetFieldName,
            ]
        ];
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
