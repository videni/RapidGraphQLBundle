<?php

namespace Videni\Bundle\RestBundle\Serializer\Handler;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\Context;
use Videni\Bundle\RestBundle\Normalizer\FormView\FormViewNormalizer;

class FormDataNormalizer implements SubscribingHandlerInterface
{
    private $formViewNormalizer;

    public function __construct(FormViewNormalizer $formViewNormalizer)
    {
        $this->formViewNormalizer = $formViewNormalizer;
    }

    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => FormView::class,
                'method' => 'normalize',
            ],
        ];
    }

    public function normalize(JsonSerializationVisitor $visitor, FormView $formView, array $type, Context $context)
    {
        if (!$context->hasAttribute('form') || !$context->getAttribute('form') instanceof FormInterface) {
            throw new \LogicException(
                sprintf(
                    'The form attribute is not valid, an instance of %s in the serialization context must be set when serialize FormView',
                    FormInterface::class
                )
            );
        }
        $form = $context->getAttribute('form');

        $data = $this->formViewNormalizer->normalize($form, $formView, $context);

        return $data;
    }

}
