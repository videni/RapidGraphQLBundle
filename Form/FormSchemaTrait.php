<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\Form;

use Limenius\Liform\Liform;
use Videni\Bundle\RapidGraphQLBundle\Serializer\UiSchema;
use Symfony\Component\Form\FormInterface;

trait FormSchemaTrait
{
    protected $liform;

    public function __construct(Liform $liform)
    {
        $this->liform = $liform;
    }

    public function createFormSchema(FormInterface $form)
    {
        $schema = $this->liform->transform($form);
        $uiSchema = UiSchema::extract($schema);

        return [
            'formData' => $form->createView(),
            'schema' => $schema,
            'uiSchema' => (object)$uiSchema,
        ];
    }
}
