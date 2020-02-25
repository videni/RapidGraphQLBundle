<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Overblog\GraphQLBundle\Validator\Exception\ArgumentsValidationException;
use Symfony\Component\Validator\ConstraintViolationList;
use Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver\DataPersister;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormError;

class FormHandler implements FormHandlerInterface
{
    public function onSuccess(FormInterface $form): void
    {

    }

    public function onFailed(FormInterface $form): void
    {
        $violations = [];
        $this->convertFormToArray($form, $violations, '');

        throw new ArgumentsValidationException(new ConstraintViolationList($violations));
    }

    private function convertFormToArray(FormInterface $form, &$violations, $previousPath): void
    {
        $errors = [];
        $currentPath = $previousPath.'.'.$form->getName();

        foreach ($form->getErrors() as $error) {
            $cause = $error->getCause();
            $violation = new ConstraintViolation(
                $error->getMessage(),
                $error->getMessageTemplate(),
                $error->getMessageParameters(),
                $cause ? $cause->getRoot(): null,
                $currentPath,
                $cause ? $cause->getInvalidValue(): null,
                $cause ? $cause->getPlural(): null,
                $cause ? $cause->getCode(): null,
                $cause ? $cause->getConstraint(): null,
                $cause
            );

            $violations[] = $violation;
        }

        foreach ($form->all() as $child) {
            if ($child instanceof FormInterface) {
                $this->convertFormToArray($child, $violations, $currentPath);
            }
        }
    }
}
