<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Form;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Limenius\Liform\Liform;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;

class FormHandler
{
    protected $serializer;
    protected $liform;

    public function __construct(
        SerializerInterface $serializer,
        Liform $liform
    ) {
        $this->serializer = $serializer;
        $this->liform = $liform;
    }

    public function createResponse($request, $data, $status, SerializationContext $context)
    {
        $format = $request->getRequestFormat();

        return new Response(
            $this->serializer->serialize($data, $format, $context),
            $status,
            [
                'Content-Type' => sprintf('%s; charset=utf-8', $request->getMimeType($format)),
                'Vary' => 'Accept',
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'deny',
            ]
        );
    }

    public function createSerializationContext(FormInterface $form)
    {
        $context = new SerializationContext();
        $context->setAttribute('form', $form);
        $context->setAttribute('form_schema_on_validation_error', true);

        return $context;
    }

    public function createFormSchema(FormInterface $form)
    {
       return [
            'form' => [
                'data' => $form->createView(),
                'schema' => $this->liform->transform($form),
            ],
        ];
    }
}
