<?php

namespace Videni\Bundle\RestBundle\Processor\Shared;

use Videni\Bundle\RestBundle\Form\FormValidationHandler;
use Videni\Bundle\RestBundle\Processor\FormContext;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Validator\EventListener\ValidationListener;
use Symfony\Component\Form\Extension\Validator\ViolationMapper\ViolationMapper;
use Symfony\Component\Form\FormEvent;
use Videni\Bundle\RestBundle\Validator\Exception\ValidationException;

/**
 * Performs the validation of the request data via the form from the context.
 */
class ValidateForm implements ProcessorInterface
{
   /** @var ValidatorInterface */
    protected $validator;

    /** @var PropertyAccessorInterface */
    private $propertyAccessor;

    /**
     * @param ValidatorInterface        $validator
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(
        ValidatorInterface $validator,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->validator = $validator;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var FormContext $context */

        if ($context->isFormValidationSkipped()) {
            // the form validation was not requested for this action
            return;
        }

        $form = $context->getForm();
        if (null === $form || !$form->isSubmitted()) {
            // the form does not exist or not submitted yet
            return;
        }

        $violations = $this->validate($form);
        if (0 !== \count($violations)) {
            throw new ValidationException($violations);
        }
    }

    protected function validate(FormInterface $form)
    {
        if (!$form->isRoot()) {
            throw new \InvalidArgumentException('The root form is expected.');
        }
        if (!$form->isSubmitted()) {
            throw new \InvalidArgumentException('The submitted form is expected.');
        }

        return $this->validator->validate($form);
    }
}
