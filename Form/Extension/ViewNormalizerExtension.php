<?php

namespace Videni\Bundle\RapidGraphQLBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormView;
use JMS\Serializer\Context;
use Symfony\Component\Form\FormInterface;

/**
 * Adds a 'view_normalizer' configuration option to instances of FormType
 */
class ViewNormalizerExtension extends AbstractTypeExtension
{
    /**
     * Returns the name of the type being extended.
     *
     * @return string
     */
    public function getExtendedType()
    {
        return FormType::class;
    }

    /**
     * Add the ui option
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(['view_normalizer'])
            ->setDefaults([
                'view_normalizer' => null
            ]);
        ;
    }
}
