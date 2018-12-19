<?php

namespace Videni\Bundle\RestBundle\Decoder;

/**
 * Decodes JSON data.
 */
class JsonDecoder implements DecoderInterface
{
    /**
     * {@inheritdoc}
     */
    public function decode($data)
    {
        return @json_decode($data, true);
    }
}
