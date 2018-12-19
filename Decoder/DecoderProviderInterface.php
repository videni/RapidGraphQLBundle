<?php

namespace Videni\Bundle\RestBundle\Decoder;

/**
 * Defines the interface of decoder providers.
 */
interface DecoderProviderInterface
{
    /**
     * Checks if a certain format is supported.
     *
     * @param string $format
     *
     * @return bool
     */
    public function supports($format);

    /**
     * Provides decoders, possibly lazily.
     *
     * @param string $format
     *
     * @return \FOS\RestBundle\Decoder\DecoderInterface
     */
    public function getDecoder($format);
}
