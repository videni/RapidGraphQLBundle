<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Serializer;

use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\VisitorInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\DeserializationContext;

class ObjectConstructor implements ObjectConstructorInterface
{
    private $decorated;

    public function __construct(ObjectConstructorInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function construct(VisitorInterface $visitor, ClassMetadata $metadata, $data, array $type, DeserializationContext $context)
    {
        if ($context->hasAttribute('object_to_update')) {
            return $context->getAttribute('object_to_update');
        }

        return $this->decorated->construct($visitor, $metadata, $data, $type, $context);
    }
}
