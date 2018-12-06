<?php

namespace Videni\Bundle\RestBundle\Form\Guesser;

use Videni\Bundle\RestBundle\Config\Form\FormFieldConfig;
use Videni\Bundle\RestBundle\Config\Resource\ResourceConfig;
use Videni\Bundle\RestBundle\Form\Type\CollectionType;
use Videni\Bundle\RestBundle\Form\Type\CompoundObjectType;
use Videni\Bundle\RestBundle\Form\Type\EntityCollectionType;
use Videni\Bundle\RestBundle\Form\Type\EntityScalarCollectionType;
use Videni\Bundle\RestBundle\Form\Type\EntityType;
use Videni\Bundle\RestBundle\Form\Type\NestedAssociationType;
use Videni\Bundle\RestBundle\Form\Type\ScalarCollectionType;
use Videni\Bundle\RestBundle\Model\DataType;
use Videni\Bundle\RestBundle\Util\DoctrineHelper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\TypeGuess;
use Videni\Bundle\RestBundle\Config\Resource\ResourceConfigProvider;

/**
 * Guesses form types based on "form_type_guesses" configuration .
 */
class MetadataTypeGuesser implements FormTypeGuesserInterface
{
    /** @var array [data type => [form type, options], ...] */
    protected $dataTypeMappings = [];

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    protected $resourceConfigProvider;

    /**
     * @param array          $dataTypeMappings [data type => [form type, options], ...]
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        array $dataTypeMappings,
        DoctrineHelper $doctrineHelper,
        ResourceConfigProvider $resourceConfigProvider
    ) {
        $this->dataTypeMappings = $dataTypeMappings;
        $this->doctrineHelper = $doctrineHelper;
        $this->resourceConfigProvider = $resourceConfigProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function guessType($class, $property)
    {
        $resourceConifig = $this->getResourceConfigForClass($class);
        if (null !== $resourceConifig && $resourceConifig->hasFormField($property)) {
            return $this->getTypeGuessForField($resourceConifig->getFormField($property));
        }

        return $this->createDefaultTypeGuess();
    }

    /**
     * {@inheritdoc}
     */
    public function guessRequired($class, $property)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function guessMaxLength($class, $property)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function guessPattern($class, $property)
    {
        return null;
    }

    /**
     * @param string $dataType
     * @param string $formType
     * @param array  $formOptions
     */
    public function addDataTypeMapping($dataType, $formType, array $formOptions = [])
    {
        $this->dataTypeMappings[$dataType] = [$formType, $formOptions];
    }

    /**
     * @param string $class
     *
     * @return ResourceConfig|null
     */
    protected function getResourceConfigForClass($class)
    {
        return null !== $this->resourceConfigProvider
            ? $this->resourceConfigProvider->get($class)
            : null;
    }

    /**
     * @param string $class
     * @param string $property
     *
     * @return EntityDefinitionFieldConfig|null
     */
    protected function getFieldConfig($class, $property)
    {
        $config = $this->getConfigForClass($class);

        return null !== $config
            ? $config->getField($property)
            : null;
    }

    /**
     * @param string $formType
     * @param array  $formOptions
     * @param int    $confidence
     *
     * @return TypeGuess
     */
    protected function createTypeGuess($formType, array $formOptions, $confidence)
    {
        return new TypeGuess($formType, $formOptions, $confidence);
    }

    /**
     * @return TypeGuess
     */
    protected function createDefaultTypeGuess()
    {
        return $this->createTypeGuess(TextType::class, [], TypeGuess::LOW_CONFIDENCE);
    }

    /**
     * @param FormFieldConfig $formFieldConfig
     *
     * @return TypeGuess
     */
    protected function getTypeGuessForField(FormFieldConfig $formFieldConfig)
    {
        $dataType = $formFieldConfig->getDataType();
        if (isset($this->dataTypeMappings[$dataType])) {
            list($formType, $options) = $this->dataTypeMappings[$dataType];

            return $this->createTypeGuess($formType, $options, TypeGuess::HIGH_CONFIDENCE);
        }

        if (DataType::isNestedObject($dataType)) {
            return $this->getTypeGuessForNestedObject($formFieldConfig);
        }

        if (DataType::isArrayAssociation($dataType)) {
            return !$formFieldConfig->isCollapsed() ? $this->getTypeGuessForArrayAssociation($formFieldConfig): $this->getTypeGuessForCollapsedArrayAssociation($formFieldConfig);
        }

        return  $this->createDefaultTypeGuess();
    }

    /**
     * @param AssociationMetadata         $resourceConifig
     * @param EntityDefinitionFieldConfig $config
     *
     * @return TypeGuess|null
     */
    protected function getTypeGuessForArrayAssociation(
        FormFieldConfig $formFieldConfig
    ) {
        $targetClass = $formFieldConfig->getTargetClass();

        $formType = $this->doctrineHelper->isManageableEntityClass($targetClass)
            ? EntityCollectionType::class
            : CollectionType::class;

        return $this->createTypeGuess(
            $formType,
            [
                'entry_data_class' => $targetClass,
                'entry_type'       => CompoundObjectType::class,
                'entry_options'    => [
                    'config'   => $formFieldConfig
                ]
            ],
            TypeGuess::HIGH_CONFIDENCE
        );
    }

    /**
     * @param FormFieldConfig $formFieldConfig
     *
     * @return TypeGuess
     */
    protected function getTypeGuessForNestedObject(FormFieldConfig $formFieldConfig)
    {
        return $this->createTypeGuess(
            CompoundObjectType::class,
            array_merge(
                $config->getFormOptions(),
                [
                    'config'   => $formFieldConfig
                ]
            ),
            TypeGuess::HIGH_CONFIDENCE
        );
    }

    /**
     * @param AssociationMetadata $resourceConifig
     *
     * @return TypeGuess|null
     */
    protected function getTypeGuessForCollapsedArrayAssociation(FormFieldConfig $formFieldConfig)
    {
        // it is expected that collapsed association must have only one field or association
        $fieldNames = array_keys($targetMetadata->getFields());
        $targetFieldName = reset($fieldNames);
        if (!$targetFieldName) {
            $associationNames = array_keys($targetMetadata->getAssociations());
            $targetFieldName = reset($associationNames);
        }
        if (!$targetFieldName) {
            return null;
        }

        $formType = $this->doctrineHelper->isManageableEntityClass($targetMetadata->getClassName())
            ? EntityScalarCollectionType::class
            : ScalarCollectionType::class;

        return $this->createTypeGuess(
            $formType,
            [
                'entry_data_class'    => $targetMetadata->getClassName(),
                'entry_data_property' => $targetFieldName,
            ],
            TypeGuess::HIGH_CONFIDENCE
        );
    }
}
