<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Videni\Bundle\RestBundle\Context\ResourceContextStorage;
use Videni\Bundle\RestBundle\Operation\ActionTypes;

final class RespondListener
{
    const METHOD_TO_CODE = [
        'POST' => Response::HTTP_CREATED,
        'DELETE' => Response::HTTP_NO_CONTENT,
    ];

    const ACTION_TO_CODE = [
        ActionTypes::UPDATE =>  Response::HTTP_OK,
        ActionTypes::CREATE =>  Response::HTTP_CREATED,
        ActionTypes::DELETE =>  Response::HTTP_NO_CONTENT,
    ];

    private $resourceContextStorage;

    public function __construct(ResourceContextStorage $resourceContextStorage)
    {
        $this->resourceContextStorage = $resourceContextStorage;
    }

    /**
     * Creates a Response to send to the client according to the requested format.
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $controllerResult = $event->getControllerResult();
        $request = $event->getRequest();
        if ($controllerResult instanceof Response || !$request->attributes->get('_api_respond')) {
            return;
        }

        $status = self::METHOD_TO_CODE[$request->getMethod()] ?? Response::HTTP_OK;

        $context = $this->resourceContextStorage->getContext();
        if (null !== $context) {
            $status = self::ACTION_TO_CODE[$context->getActionType()]?? Response::HTTP_OK;
        }

        $headers = [
            'Content-Type' => sprintf('%s; charset=utf-8', $request->getMimeType($request->getRequestFormat())),
            'Vary' => 'Accept',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'deny',
        ];

        $event->setResponse(new Response(
            $controllerResult,
            $status,
            $headers
        ));
    }
}
