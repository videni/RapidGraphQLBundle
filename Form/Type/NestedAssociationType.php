<?php

namespace Videni\Bundle\RestBundle\Form\Type;

use Videni\Bundle\RestBundle\Config\EntityDefinitionFieldConfig;
use Videni\Bundle\RestBundle\Form\DataTransformer\NestedAssociationTransformer;
use Videni\Bundle\RestBundle\Form\EventListener\NestedAssociationListener;
use Videni\Bundle\RestBundle\Metadata\AssociationMetadata;
use Videni\Bundle\RestBundle\Util\DoctrineHelper;
use Videni\Bundle\RestBundle\Util\EntityLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * The form type for manageable entity nested associations.
 */
class NestedAssociationType extends AbstractType
{
    /** @var PropertyAccessorInterface */
    protected $propertyAccessor;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var EntityLoader */
    protected $entityLoader;

    /**
     * @param PropertyAccessorInterface $propertyAccessor
     * @param DoctrineHelper            $doctrineHelper
     * @param EntityLoader              $entityLoader
     */
    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        DoctrineHelper $doctrineHelper,
        EntityLoader $entityLoader
    ) {
        $this->propertyAccessor = $propertyAccessor;
        $this->doctrineHelper = $doctrineHelper;
        $this->entityLoader = $entityLoader;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventSubscriber(new NestedAssociationListener($this->propertyAccessor, $options['config']))
            ->addViewTransformer(
                new NestedAssociationTransformer($this->doctrineHelper, $this->entityLoader, $options['metadata'])
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(['compound' => false])
            ->setRequired(['metadata', 'config'])
            ->setAllowedTypes('metadata', [AssociationMetadata::class])
            ->setAllowedTypes('config', [EntityDefinitionFieldConfig::class]);
    }
}
