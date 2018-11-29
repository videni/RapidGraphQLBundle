<?php


declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Validator\EventListener;

use Videni\Bundle\RestBundle\Validator\Exception\ValidationException;
use Videni\Bundle\RestBundle\Util\ErrorFormatGuesser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use JMS\Serializer\SerializerInterface;

final class ValidationExceptionListener
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Returns a list of violations normalized in the Hydra format.
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (!$exception instanceof ValidationException) {
            return;
        }

        $request = $event->getRequest();
        $format = $request->getRequestFormat('json');

        $event->setResponse(new Response(
            $this->serializer->serialize($exception->getConstraintViolationList(), $format),
            Response::HTTP_BAD_REQUEST,
            [
                'Content-Type' => sprintf('%s; charset=utf-8', $request->getMimeType($format)),
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'deny',
            ]
        ));
    }
}
