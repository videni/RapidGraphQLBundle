<?php

namespace Videni\Bundle\RestBundle\Normalizer\FormView;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Limenius\Liform\FormUtil;
use JMS\Serializer\Context;

class ChoiceFormViewNormalizer implements FormViewNormalizerInterface
{
    public function support(FormInterface $form, FormView $formView, $ancestries = [])
    {
        return in_array('choice', $ancestries) &&
            $formView->vars['expanded'];
    }

    public function normalize(FormInterface $form, FormView $formView, Context $context)
    {
        if ($formView->vars['multiple']) {
            return $this->normalizeMultipleExpandedChoice($formView);
        }

        return $this->normalizeExpandedChoice($formView);
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
