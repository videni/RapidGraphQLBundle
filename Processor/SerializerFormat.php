<?php

namespace Videni\Bundle\RestBundle\Processor;

use Videni\Bundle\RestBundle\Provider\FormatsProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class SerializerFormat
{
    private $formatsProvider;

    public function __construct(
        FormatsProviderInterface $formatsProvider
    ) {
        $this->formatsProvider = $formatsProvider;
    }


      /**
     * Extracts the format from the Content-Type header and check that it is supported.
     *
     * @param Request $request
     * @param Context $context
     *
     * @throws NotAcceptableHttpException
     *
     * @return string
     */
    public function getFormat(Request $request, Context $context): string
    {
        $formats = $this->formatsProvider->getFormats($context->getResourceConfig(), $context->getOperationName());

        /**
         * @var string|null
         */
        $contentType = $request->headers->get('CONTENT_TYPE');

        if (null === $contentType) {
            throw new NotAcceptableHttpException('The "Content-Type" header must exist.');
        }

        $format = $request->getFormat($contentType);
        if (null === $format || !isset($formats[$format])) {
            $supportedMimeTypes = [];
            foreach ($formats as $mimeTypes) {
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
