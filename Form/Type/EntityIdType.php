<?php

namespace Videni\Bundle\RestBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Videni\Bundle\RestBundle\Form\DataTransformer\EntityToIdTransformer;
use Doctrine\ORM\EntityManager;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityIdType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entityManager = $options['entity_manager'];
        $entityClass = $options['entity_class'];

        $builder->addModelTransformer(new EntityToIdTransformer($entityManager, $entityClass, 'id'));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(['compound' => false])
            ->setRequired(['entity_manager', 'entity_class'])
            ->setAllowedTypes('entity_manager', [EntityManager::class])
            ->setAllowedTypes('entity_class', ['string']);
    }
}
