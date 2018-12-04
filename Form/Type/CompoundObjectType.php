<?php

namespace Videni\Bundle\RestBundle\Form\Type;

use Videni\Bundle\RestBundle\Config\Form\FormFieldConfig;
use Videni\Bundle\RestBundle\Form\EventListener\CompoundObjectListener;
use Videni\Bundle\RestBundle\Form\FormHelper;
use Videni\Bundle\RestBundle\Metadata\EntityMetadata;
use Videni\Bundle\RestBundle\Metadata\PropertyMetadata;
use Videni\Bundle\RestBundle\Request\DataType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for an object that properties are built based of Data API metadata
 * and contain only properties classified as fields and associations that should be represented as a field.
 * Usually this form type is used if an object should be represented as a field in Data API.
 * @see \Videni\Bundle\RestBundle\Request\DataType::isAssociationAsField
 */
class CompoundObjectType extends AbstractType
{
    /** @var FormHelper */
    protected $formHelper;

    /**
     * @param FormHelper $formHelper
     */
    public function __construct(FormHelper $formHelper)
    {
        $this->formHelper = $formHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var EntityMetadata $metadata */
        $metadata = $options['metadata'];
        /** @var EntityDefinitionConfig $config */
        $config = $options['config'];

        $fields = $metadata->getFields();
        foreach ($fields as $name => $field) {
            $this->addFormField($builder, $config, $name, $field);
        }
        $associations = $metadata->getAssociations();
        foreach ($associations as $name => $association) {
            if (DataType::isAssociationAsField($association->getDataType())) {
                $this->addFormField($builder, $config, $name, $association);
            }
        }

        $builder->addEventSubscriber(new CompoundObjectListener());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['metadata', 'config'])
            ->setAllowedTypes('metadata', [EntityMetadata::class])
            ->setAllowedTypes('config', [EntityDefinitionConfig::class]);
    }

    /**
     * @param FormBuilderInterface   $formBuilder
     * @param EntityDefinitionConfig $config
     * @param string                 $fieldName
     * @param PropertyMetadata       $fieldMetadata
     */
    private function addFormField(
        FormBuilderInterface $formBuilder,
        EntityDefinitionConfig $config,
        $fieldName,
        PropertyMetadata $fieldMetadata
    ) {
        $this->formHelper->addFormField(
            $formBuilder,
            $fieldName,
            $config->getField($fieldName),
            $fieldMetadata,
            ['required' => false]
        );
    }
}
