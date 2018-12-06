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
        /** @var FormFieldConfig $config */
        $config = $options['field_config'];
        $fields = $config->getFields();

        foreach ($fields as $name => $field) {
            $this->addFormField($builder, $name, $field);
        }

        $builder->addEventSubscriber(new CompoundObjectListener());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired([ 'field_config'])
            ->setAllowedTypes('field_config', [FormFieldConfig::class]);
    }

    /**
     * @param FormBuilderInterface   $formBuilder
     * @param string                 $fieldName
     * @param FormFieldConfig $formFieldConfig
     */
    private function addFormField(
        FormBuilderInterface $formBuilder,
        $fieldName,
        FormFieldConfig $formFieldConfig
    ) {
        $this->formHelper->addFormField(
            $formBuilder,
            $fieldName,
            $formFieldConfig,
            ['required' => false]
        );
    }
}
