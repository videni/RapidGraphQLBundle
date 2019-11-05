<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\EventListener;

use Overblog\GraphQLBundle\Event\ExecutorArgumentsEvent;
use Videni\Bundle\RapidGraphQLBundle\GraphQL\ExecutorContextStorage;


final class RequestExecutorListener
{
    private $contextStorage;

    public function __construct(ExecutorContextStorage $contextStorage)
    {
        $this->contextStorage = $contextStorage;
    }

    public function onPreExecutor(ExecutorArgumentsEvent $event): void
    {
        $value = $event->getContextValue();

        //Inject root schema name into context
        $value['schema'] = $event->getSchemaName();

        $this->contextStorage->setValue($value);
    }
}
