<?php

namespace Videni\Bundle\RestBundle\Form\Extension;

use Videni\Bundle\RestBundle\Form\DataTransformer\DateTimeToLocalizedStringTransformer as Wrapper;
use Videni\Bundle\RestBundle\Form\DataTransformer\NullValueTransformer;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToLocalizedStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Wraps "Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToLocalizedStringTransformer"
 * with "Videni\Bundle\RestBundle\Form\DataTransformer\DateTimeToLocalizedStringTransformer"
 * to prevent timezone conversion in "reverseTransform" method for case if the input string contains
 * a date without the time.
 */
class DateTimeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $viewTransformers = $builder->getViewTransformers();
        foreach ($viewTransformers as $key => $viewTransformer) {
            if ($viewTransformer instanceof NullValueTransformer
                && $viewTransformer->getInnerTransformer() instanceof DateTimeToLocalizedStringTransformer
            ) {
                $viewTransformer->setInnerTransformer(new Wrapper($viewTransformer->getInnerTransformer()));
                break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return DateTimeType::class;
    }
}
