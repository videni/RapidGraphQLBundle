<?php

namespace Videni\Bundle\RestBundle\Normalizer;

/**
 * Normalizes arrays.
 */
interface ArrayNormalizerInterface
{
    /**
     * Normalizes the array.
     *
     * @param array $data The array to normalize
     *
     * @throws Exception\NormalizationException
     *
     * @return array The normalized array
     */
    public function normalize(array $data);
}
