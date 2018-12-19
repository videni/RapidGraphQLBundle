<?php

namespace Videni\Bundle\RestBundle\Decoder;

/**
 * Defines the interface of decoders.
 */
interface DecoderInterface
{
    /**
     * Decodes a string into PHP data.
     *
     * @param string $data
     *
     * @return mixed False in case the content could not be decoded
     */
    public function decode($data);
}
