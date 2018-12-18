<?php

namespace Videni\Bundle\RestBundle\EventListener;

use Negotiation\Negotiator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Videni\Bundle\RestBundle\Provider\FormatsProviderInterface;
use Videni\Bundle\RestBundle\Context\ResourceContextStorage;

class FormatListener
{
    private $negotiator;
    private $formats = [];
    private $mimeTypes;
    private $formatsProvider;
    private $resourceContextStorage;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        Negotiator $negotiator,
        FormatsProviderInterface $formatsProvider,
        ResourceContextStorage $resourceContextStorage
    ) {
        $this->negotiator = $negotiator;
        $this->formatsProvider = $formatsProvider;
        $this->resourceContextStorage = $resourceContextStorage;
    }

    /**
     * Sets the applicable format to the HttpFoundation Request.
     *
     * @throws NotFoundHttpException
     * @throws NotAcceptableHttpException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->attributes->has('_api_resource_class') && !$request->attributes->has('_api_respond')) {
            return;
        }

        $resourceContext = $this->resourceContextStorage->getContext();

        $this->formats = $this->formatsProvider->getFormats($resourceContext, $resourceContext->getOperationName());

        $this->populateMimeTypes();
        $this->addRequestFormats($request, $this->formats);

        // Empty strings must be converted to null because the Symfony router doesn't support parameter typing before 3.2 (_format)
        if (null === $routeFormat = $request->attributes->get('_format') ?: null) {
            $mimeTypes = array_keys($this->mimeTypes);
        } elseif (!isset($this->formats[$routeFormat])) {
            throw new NotFoundHttpException(sprintf('Format "%s" is not supported', $routeFormat));
        } else {
            $mimeTypes = Request::getMimeTypes($routeFormat);
        }

        // First, try to guess the format from the Accept header
        /** @var string|null $accept */
        $accept = $request->headers->get('Accept');
        if (null !== $accept) {
            if (null === $acceptHeader = $this->negotiator->getBest($accept, $mimeTypes)) {
                throw $this->getNotAcceptableHttpException($accept, $mimeTypes);
            }

            $request->setRequestFormat($request->getFormat($acceptHeader->getType()));

            return;
        }

        // Then use the Symfony request format if available and applicable
        $requestFormat = $request->getRequestFormat('') ?: null;
        if (null !== $requestFormat) {
            $mimeType = $request->getMimeType($requestFormat);

            if (isset($this->mimeTypes[$mimeType])) {
                return;
            }

            throw $this->getNotAcceptableHttpException($mimeType);
        }

        // Finally, if no Accept header nor Symfony request format is set, return the default format
        foreach ($this->formats as $format => $mimeType) {
            $request->setRequestFormat($format);

            return;
        }
    }

    /**
     * Adds API formats to the HttpFoundation Request.
     */
    private function addRequestFormats(Request $request, array $formats)
    {
        foreach ($formats as $format => $mimeTypes) {
            $request->setFormat($format, $mimeTypes);
        }
    }

    /**
     * Populates the $mimeTypes property.
     */
    private function populateMimeTypes()
    {
        if (null !== $this->mimeTypes) {
            return;
        }

        $this->mimeTypes = [];
        foreach ($this->formats as $format => $mimeTypes) {
            foreach ($mimeTypes as $mimeType) {
                $this->mimeTypes[$mimeType] = $format;
            }
        }
    }

    /**
     * Retrieves an instance of NotAcceptableHttpException.
     *
     * @param string[]|null $mimeTypes
     */
    private function getNotAcceptableHttpException(string $accept, array $mimeTypes = null): NotAcceptableHttpException
    {
        if (null === $mimeTypes) {
            $mimeTypes = array_keys($this->mimeTypes);
        }

        return new NotAcceptableHttpException(sprintf(
            'Requested format "%s" is not supported. Supported MIME types are "%s".',
            $accept,
            implode('", "', $mimeTypes)
        ));
    }
}
