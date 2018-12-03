<?php

namespace Videni\Bundle\RestBundle\Form\Type;

use Videni\Bundle\RestBundle\Config\Entity\FormConfig;
use Videni\Bundle\RestBundle\Form\FormHelper;
use Videni\Bundle\RestBundle\Metadata\EntityMetadata;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for an object that properties are built based of Data API metadata
 * and contain all properties classified as fields and associations.
 */
class ObjectType extends AbstractType
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

        $this->formHelper->addFormFields($builder, $metadata, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['metadata', 'config'])
            ->setAllowedTypes('metadata', ['Videni\Bundle\RestBundle\Metadata\EntityMetadata'])
            ->setAllowedTypes('config', ['Videni\Bundle\RestBundle\Config\Entity\FormConfig']);
    }
}
