<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Overblog\GraphQLBundle\Validator\Exception\ArgumentsValidationException;
use Symfony\Component\Validator\ConstraintViolationList;
use Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver\DataPersister;

class FormHandler implements FormHandlerInterface
{
    public function onSuccess(FormInterface $form): void
    {

    }

    public function onFailed(FormInterface $form): void
    {
        $violations = [];
        foreach($form->getErrors(true) as $error) {
            $violations[] = $error->getCause();
        }

        throw new ArgumentsValidationException(new ConstraintViolationList($violations));
    }
}
