<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Overblog\GraphQLBundle\Validator\Exception\ArgumentsValidationException;
use Symfony\Component\Validator\ConstraintViolationList;
use Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver\DataPersister;
use Symfony\Component\Validator\ConstraintViolation;

class FormHandler implements FormHandlerInterface
{
    public function onSuccess(FormInterface $form): void
    {

    }

    public function onFailed(FormInterface $form): void
    {
        $violations = [];
        foreach($form->getErrors(true) as $error) {
            /**
             * $cause ConstraintViolation
             */
            $cause = $error->getCause();
            $origin = $error->getOrigin();

            $propertyPaths[] = (string)$origin->getPropertyPath();
            for( $parent = $origin->getParent(); $parent !==null; $parent = $parent->getParent()) {
                array_unshift($propertyPaths, (string)$parent->getPropertyPath());
            }

            $violations[] = new ConstraintViolation(
                $error->getMessage(),
                $error->getMessageTemplate(),
                $error->getMessageParameters(),
                $cause ? $cause->getRoot(): null,
                implode('.', $propertyPaths),
                $cause ? $cause->getInvalidValue(): null,
                $cause ? $cause->getPlural(): null,
                $cause ? $cause->getCode(): null,
                $cause ? $cause->getConstraint(): null,
                $cause
            );
        }

        throw new ArgumentsValidationException(new ConstraintViolationList($violations));
    }
}
