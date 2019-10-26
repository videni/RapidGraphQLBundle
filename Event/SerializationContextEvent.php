<?php

namespace Videni\Bundle\RapidGraphQLBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use JMS\Serializer\Context;
use Videni\Bundle\RapidGraphQLBundle\Context\ResourceContext;

class SerializationContextEvent extends Event
{
    const EVENT_NAME = 'videni_rapid_graphql.serialization_context';

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
