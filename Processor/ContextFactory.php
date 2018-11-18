<?php

namespace App\Bundle\RestBundle\Processor;

use App\Bundle\RestBundle\Provider\FormatsProviderInterface;
use App\Bundle\RestBundle\Processor\ActionProcessorBagInterface;
use App\Bundle\RestBundle\Request\RestRequestHeaders;

class ContextFactory
{
    private $formatsProvider;
    private $actionProcessorBag;

    public function __construct(
        ActionProcessorBagInterface $actionProcessorBag,
        FormatsProviderInterface $formatsProvider
    ) {
        $this->actionProcessorBag = $actionProcessorBag;
        $this->formatsProvider = $formatsProvider;
    }

    /**
     * @param ActionProcessorInterface $processor
     * @param Request                  $request
     *
     * @return Context
     */
    public function create(Request $request)
    {
        $context = $this->initialize();
        $context->setFormat($this->getFormat($request));

        return $context;
    }

    private function initialize(Request $request)
    {
        $processor = $this->getProcessor($request);

        /** @var Context $context */
        $context = $processor->createContext();
        $context->setClassName($request->attributes->get('_api_resource_class'));
        $context->setOperationName($request->attributes->get('_api_operation_name'));
        $context->setRequestHeaders(new RestRequestHeaders($request));

        $context->loadMetadata();

        return $context;
    }

     /**
     * @param Request $request
     *
     * @return ActionProcessorInterface
     */
    private function getProcessor(Request $request)
    {
        return $this->actionProcessorBag->getProcessor($request->attributes->get('_action'));
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
    private function getFormat(Request $request, Context $context): string
    {
        $formats = $this->formatsProvider->getFormats($context->getMetadata(), $context->getOperationName());

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
