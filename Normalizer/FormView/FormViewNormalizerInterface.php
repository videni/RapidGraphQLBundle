<?php

namespace Videni\Bundle\RapidGraphQLBundle\Normalizer\FormView;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use JMS\Serializer\Context;

interface FormViewNormalizerInterface
{
    public function support(FormInterface $form, FormView $formView, array $ancestries);

    public function normalize(FormInterface $form, FormView $formView, Context $context);
}
