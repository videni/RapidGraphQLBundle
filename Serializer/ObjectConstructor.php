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
        if ($context->hasAttribute('target') && $context->getDepth() === 1 && null !== $context->getAttribute('target')) {
            return $context->getAttribute('target');
        }

        return $this->decorated->construct($visitor, $metadata, $data, $type, $context);
    }
}
