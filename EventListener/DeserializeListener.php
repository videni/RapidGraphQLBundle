<?php

namespace App\Bundle\RestBundle\EventListener;

use App\Bundle\RestBundle\Exception\InvalidArgumentException;
use App\Bundle\RestBundle\Utils\AttributesExtractor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use JMS\Serializer\SerializerInterface;
use App\Bundle\RestBundle\Provider\FormatsProviderInterface;
use JMS\Serializer\DeserializationContext;
use App\Bundle\RestBundle\Serializer\SerializerContextBuilderInterface;

final class DeserializeListener
{
    private $serializer;
    private $formatsProvider;
    private $serializerContextBuilder;
    private $formats;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        SerializerInterface $serializer,
        FormatsProviderInterface $formatsProvider,
        SerializerContextBuilderInterface $serializerContextBuilder
    ) {
        $this->serializer = $serializer;
        $this->serializerContextBuilder = $serializerContextBuilder;
        $this->formatsProvider = $formatsProvider;
    }

    /**
     * Deserializes the data sent in the requested format.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $method = $request->getMethod();
        if ($request->isMethodSafe(false)
            || 'DELETE' === $method
            || !($attributes = AttributesExtractor::extractAttributes($request->attributes->all()))
            || !$attributes['receive']
            || (
                    '' === ($requestContent = $request->getContent())
                    && ('POST' === $method || 'PUT' === $method)
               )
        ) {
            return;
        }

        $this->formats = $this->formatsProvider->getFormatsFromAttributes($attributes);
        $format = $this->getFormat($request);

        $context = $this->serializerContextBuilder->createFromRequest($request, false, $attributes);

        $data = $request->attributes->get('data');
        if (null !== $data) {
            $context->setAttribute('data', $data);
        }

        $data = $this->serializer->deserialize(
            $requestContent,
            $attributes['resource_class'],
            $format,
            $context
        );

        $request->attributes->set(
            'data',
            $data
        );
    }

    /**
     * Extracts the format from the Content-Type header and check that it is supported.
     *
     * @param Request $request
     *
     * @throws NotAcceptableHttpException
     *
     * @return string
     */
    private function getFormat(Request $request): string
    {
        /**
         * @var string|null
         */
        $contentType = $request->headers->get('CONTENT_TYPE');
        if (null === $contentType) {
            throw new NotAcceptableHttpException('The "Content-Type" header must exist.');
        }

        $format = $request->getFormat($contentType);

        if (null === $format || !isset($this->formats[$format])) {
            $supportedMimeTypes = [];
            foreach ($this->formats as $mimeTypes) {
                foreach ($mimeTypes as $mimeType) {
                    $supportedMimeTypes[] = $mimeType;
                }
            }

            throw new NotAcceptableHttpException(sprintf(
                'The content-type "%s" is not supported. Supported MIME types are "%s".',
                $contentType,
                implode('", "', $supportedMimeTypes)
            ));
        }

        return $format;
    }
}
