<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;

interface FormHandlerInterface
{
    public function onSuccess(FormInterface $form): void;
    public function onFailed(FormInterface $form): void;
}
