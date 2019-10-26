<?php

namespace Videni\Bundle\RapidGraphQLBundle\Serializer\Handler;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormView;
use Limenius\Liform\FormUtil;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\Context;
use Limenius\Liform\Liform;

class FormErrorNormalizer implements SubscribingHandlerInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    private $liform;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator, Liform $liform)
    {
        $this->translator = $translator;
        $this->liform = $liform;
    }

    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => Form::class,
                'method' => 'normalize',
            ],
        ];
    }

    public function normalize(JsonSerializationVisitor $visitor, FormInterface $form, array $type, Context $context)
    {
        if(!$this->supportsNormalization($form)) {
            return;
        }

        $data = [
            'message' => 'Validation Failed',
            'errors' => $this->convertFormToArray($form),
        ];

        if ($context->hasAttribute('form_schema_on_validation_error') && $context->getAttribute('form_schema_on_validation_error')) {
            return $data + ['form' => $this->createFormSchema($form, $context)];
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FormInterface && $data->isSubmitted() && !$data->isValid();
    }

    protected function createFormSchema(FormInterface $form, Context $context)
    {
       return [
            'data' => $context->getNavigator()->accept($form->createView()),
            'schema' => $this->liform->transform($form),
        ];
    }

    /**
     * This code has been taken from JMSSerializer.
     *
     * @param FormInterface $data
     *
     * @return array
     */
    private function convertFormToArray(FormInterface $data)
    {
        $form = $errors = [];
        foreach ($data->getErrors() as $error) {
            $errors[] = $this->getErrorMessage($error);
        }

        if ($errors) {
            $form['errors'] = $errors;
        }

        $children = [];
        foreach ($data->all() as $child) {
            if ($child instanceof FormInterface) {
                $childErrors = $this->convertFormToArray($child);
                if ($childErrors !== []) {
                    $children[$child->getName()] = $childErrors;
                }
            }
        }

        if ($children) {
            $form['children'] = $children;
        }

        return $form;
    }

    /**
     * @param FormError $error
     *
     * @return string
     */
    private function getErrorMessage(FormError $error)
    {
        if (null !== $error->getMessagePluralization()) {
            return $this->translator->transChoice($error->getMessageTemplate(), $error->getMessagePluralization(), $error->getMessageParameters(), 'validators');
        }

        return $this->translator->trans($error->getMessageTemplate(), $error->getMessageParameters(), 'validators');
    }
}
