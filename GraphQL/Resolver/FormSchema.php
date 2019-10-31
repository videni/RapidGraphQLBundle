<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Symfony\Component\Form\FormInterface;
use Limenius\Liform\Liform;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Videni\Bundle\RapidGraphQLBundle\Serializer\UiSchema;

class FormSchema implements ResolverInterface
{
    private $serializer;
    private $liform;

    public function __construct(
        SerializerInterface $serializer,
        Liform $liform
    ) {
        $this->serializer = $serializer;
        $this->liform = $liform;
    }

    public function __invoke(FormInterface $value)
    {
        return $this->createDeferredObject($value);
    }

    private function createDeferredObject($value)
    {
        return new class($this->serializer, $this->liform, $value) extends FormSchema {
            private $serializer;
            private $liform;

            private $cache = null;

            private $form;

            public function __construct(
                SerializerInterface $serializer,
                Liform $liform,
                FormInterface $form
            ) {
                $this->serializer = $serializer;
                $this->liform = $liform;
                $this->form = $form;
            }

            public function getSchema()
            {
                if ($this->cache) {
                    return $this->cache;
                }

                $this->cache = $this->liform->transform($this->form);

                return $this->cache;
            }

            public function getFormData()
            {
                $context = new SerializationContext();
                $context
                    ->setAttribute('form', $this->form)
                    ->setAttribute('extra_context', new \ArrayObject());

                return $this->serializer->serialize($this->form->createView() , 'json', $context);
            }

            public function getUiSchema()
            {
                if (!$this->cache) {
                    $this->cache = $this->liform->transform($this->form);
                }

                $schema = $this->cache;

                return (object)UiSchema::extract($schema);
            }
        };
    }
}
