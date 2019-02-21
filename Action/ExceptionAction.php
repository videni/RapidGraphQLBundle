<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Action;

use ApiPlatform\Core\Util\ErrorFormatGuesser;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializerInterface;

final class ExceptionAction
{
    private $serializer;
    private $exceptionToStatus;
    private $debug;

    /**
     * @param SerializerInterface $serializer
     * @param array               $errorFormats      A list of enabled formats, the first one will be the default
     * @param array               $exceptionToStatus A list of exceptions mapped to their HTTP status code
     */
    public function __construct(
        SerializerInterface $serializer,
        array $exceptionToStatus = [],
        $debug = false
    ) {
        $this->serializer = $serializer;
        $this->exceptionToStatus = $exceptionToStatus;
        $this->debug = $debug;
    }

    /**
     * Converts a an exception to a JSON response.
     *
     * @param FlattenException $exception
     * @param Request          $request
     *
     * @return Response
     */
    public function __invoke(FlattenException $exception, Request $request): Response
    {
        $exceptionClass = $exception->getClass();

        $statusCode = $exception->getStatusCode();

        foreach ($this->exceptionToStatus as $class => $status) {
            if (is_a($exceptionClass, $class, true)) {
                $statusCode = $status;

                break;
            }
        }

        $headers = $exception->getHeaders();
        $format = $request->getRequestFormat();

        $headers['Content-Type'] = sprintf('%s; charset=utf-8', $format);
        $headers['X-Content-Type-Options'] = 'nosniff';
        $headers['X-Frame-Options'] = 'deny';

        $newException = [
            "code" => $statusCode,
            "message" => $exception->getMessage(),
        ];

        return new Response(
            $this->serializer ->serialize(
                $this->debug ? $exception : $newException,
                $format
            ),
            $statusCode,
            $headers
        );
    }
}
