<?php

namespace Videni\Bundle\RestBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Videni\Bundle\RestBundle\Normalizer\ArrayNormalizerInterface;
use Videni\Bundle\RestBundle\Normalizer\Exception\NormalizationException;
use Videni\Bundle\RestBundle\Decoder\DecoderProviderInterface;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BodyListener
{
    private $arrayNormalizer;
    private $decoderProvider;

    private $throwExceptionOnUnsupportedContentType;

    public function __construct(
        ArrayNormalizerInterface $arrayNormalizer,
        DecoderProviderInterface $decoderProvider,
        $throwExceptionOnUnsupportedContentType = false
    ) {
        $this->arrayNormalizer = $arrayNormalizer;
        $this->decoderProvider = $decoderProvider;
        $this->throwExceptionOnUnsupportedContentType = $throwExceptionOnUnsupportedContentType;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $normalizeRequest = false;

        if ($this->isDecodeable($request)) {
            $format = $request->getRequestFormat();
            $content = $request->getContent();

           if (!$this->decoderProvider->supports($format)) {
                if ($this->throwExceptionOnUnsupportedContentType
                    && $this->isNotAnEmptyDeleteRequestWithNoSetContentType($method, $content, $contentType)
                ) {
                    throw new UnsupportedMediaTypeHttpException("Request body format '$format' not supported");
                }

                return;
            }

            if (!empty($content)) {
                $decoder = $this->decoderProvider->getDecoder($format);
                $data = $decoder->decode($content);
                if (is_array($data)) {
                    $request->request = new ParameterBag($data);
                    $normalizeRequest = true;
                } else {
                    throw new BadRequestHttpException('Invalid '.$format.' message received');
                }
            }
        }

        if ($normalizeRequest) {
            $data = $request->request->all();

            try {
                $data = $this->arrayNormalizer->normalize($data);
            } catch (NormalizationException $e) {
                throw new BadRequestHttpException($e->getMessage());
            }

            $request->request = new ParameterBag($data);
        }
    }

    /**
     * Check if we should try to decode the body.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isDecodeable(Request $request)
    {
        if (!in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return false;
        }

        return !$this->isFormRequest($request);
    }

    /**
     * Check if the content type indicates a form submission.
     *
     * @param Request $request
     *
     * @return bool
     */
    private function isFormRequest(Request $request)
    {
        $contentTypeParts = explode(';', $request->headers->get('Content-Type'));

        if (isset($contentTypeParts[0])) {
            return in_array($contentTypeParts[0], ['multipart/form-data', 'application/x-www-form-urlencoded']);
        }

        return false;
    }

    /**
     * Check if the Request is not a DELETE with no content and no Content-Type.
     *
     * @param $method
     * @param $content
     * @param $contentType
     *
     * @return bool
     */
    private function isNotAnEmptyDeleteRequestWithNoSetContentType($method, $content, $contentType)
    {
        return false === ('DELETE' === $method && empty($content) && empty($contentType));
    }
}
