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

    private $cache;

    public function __construct(
        SerializerInterface $serializer,
        Liform $liform
    ) {
        $this->serializer = $serializer;
        $this->liform = $liform;
    }

    public function __invoke(FormInterface $value)
    {
        $object = new \stdClass();

        $object->formData = call_user_func([$this, 'getFormData'], $value);
        $object->schema = call_user_func([$this, 'getSchema'], $value);
        $object->uiSchema = call_user_func([$this, 'getUiSchema'], $value);

        return $object;
    }

    public function getSchema($value)
    {
        if ($this->cache) {
            return $this->cache;
        }

        $this->cache = $this->liform->transform($value);

        return $this->cache;
    }

    public function getFormData($value)
    {
        $context = new SerializationContext();
        $context
            ->setAttribute('form', $value)
            ->setAttribute('extra_context', new \ArrayObject());

        return $this->serializer->serialize($value->createView() , 'json', $context);
    }

    public function getUiSchema($value)
    {
        if (!$this->cache) {
            $this->cache = $this->liform->transform($value);
        }

        $schema = $this->cache;

        return (object)UiSchema::extract($schema);
    }
}
