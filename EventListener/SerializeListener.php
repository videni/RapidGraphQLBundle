<?php

namespace Videni\Bundle\RestBundle\EventListener;

use Videni\Bundle\RestBundle\Context\ResourceContextStorage;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;

class SerializeListener
{
    private $resourceContextStorage;

    public function __construct(SerializerInterface $serializer, ResourceContextStorage $resourceContextStorage)
    {
        $this->serializer = $serializer;
        $this->resourceContextStorage = $resourceContextStorage;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $controllerResult = $event->getControllerResult();
        $request = $event->getRequest();

        if ($controllerResult instanceof Response) {
            return;
        }

        $context = $this->resourceContextStorage->getContext();
        if (null === $context) {
            $this->serializeRawData($event, $request, $controllerResult);

            return;
        }

        $resourceConfig = $context->getResourceConfig();

        $serializationContext = new SerializationContext();

        if ($normailzationConfig = $resourceConfig->getOperationAttribute($context->getOperationName(), 'normalization_context', true)) {
            $serializationContext
                ->setGroups($normailzationConfig->getGroups())
                ->setSerializeNull(true)
            ;
        }

        $event->setControllerResult($this->serializer->serialize($controllerResult, $request->getRequestFormat(), $serializationContext));

        $request->attributes->set('_api_respond', true);
    }

       /**
     * Tries to serialize data that are not API resources (e.g. the entrypoint or data returned by a custom controller).
     *
     * @param object $controllerResult
     *
     * @throws RuntimeException
     */
    private function serializeRawData(GetResponseForControllerResultEvent $event, Request $request, $controllerResult)
    {
        if (!$request->attributes->get('_api_respond')) {
            return;
        }

        $serializationContext = new SerializationContext();
        $serializationContext->setGroups($request->attributes->get('_api_normalization_context', []));

        $event->setControllerResult($this->serializer->serialize($controllerResult, $request->getRequestFormat(), $request->attributes->get('_api_normalization_context', [])));
    }
}
