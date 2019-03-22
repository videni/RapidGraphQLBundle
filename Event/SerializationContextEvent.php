<?php

namespace Videni\Bundle\RestBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use JMS\Serializer\Context;

class SerializationContextEvent extends Event
{
    const NAME = 'videni_rest.serialization_context';

    protected $context;

    protected $resourceContext;

    public function __construct(Context $context, ResourceContext $resourceContext)
    {
        $this->context = $context;
        $this->resourceContext = $resourceContext;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getResourceContext()
    {
        return $this->resourceContext;
    }
}
