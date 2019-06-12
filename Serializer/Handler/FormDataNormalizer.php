<?php

namespace Videni\Bundle\RestBundle\Serializer\Handler;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Limenius\Liform\FormUtil;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\Context;
use Doctrine\Common\Collections\ArrayCollection;

class FormDataNormalizer implements SubscribingHandlerInterface
{
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

        $data = $this->getValues($form, $formView);

        return $data;
    }

    private function getValues(FormInterface $form, FormView $formView)
    {
        if (!empty($formView->children)) {
            if (in_array('choice', FormUtil::typeAncestry($form)) &&
                $formView->vars['expanded']
            ) {
                if ($formView->vars['multiple']) {
                    return $this->normalizeMultipleExpandedChoice($formView);
                } else {
                    return $this->normalizeExpandedChoice($formView);
                }
            }
            // Force serialization as {} instead of []
            $data = array();
            foreach ($formView->children as $name => $child) {
                $value = $child->vars['value'];
                if (empty($child->children) && $this->isEmpty($value)) {
                    continue;
                }

                $childValues = $this->getValues($form[$name], $child);
                if (!$this->isEmpty($childValues)) {
                    $data[$name] = $childValues;
                }
            }

            return $data;
        } else {
            // handle separatedly the case with checkboxes, so the result is
            // true/false instead of 1/0
            if (isset($formView->vars['checked'])) {
                return $formView->vars['checked'];
            }

            return $formView->vars['value'];
        }
    }

    /**
     * Skip empty values(null, '', [], empty ArratCollection) because
     * https://github.com/erikras/redux-form/issues/2149
     *
     * @param  mix  $value
     * @return boolean
     */
    private function isEmpty($value): bool
    {
        return $value === null || $value === '' || $value === [] || ($value instanceof ArrayCollection && $value->isEmpty());
    }

    private function normalizeMultipleExpandedChoice($formView)
    {
        $data = array();
        foreach ($formView->children as $name => $child) {
            if ($child->vars['checked']) {
                $data[] = $child->vars['value'];
            }
        }
        return $data;
    }

    private function normalizeExpandedChoice($formView)
    {
        foreach ($formView->children as $name => $child) {
            if ($child->vars['checked']) {
                return $child->vars['value'];
            }
        }
        return null;
    }
}
