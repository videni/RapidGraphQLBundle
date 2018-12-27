<?php

namespace Videni\Bundle\RestBundle\Filter\Normalizer;

interface FilterValueNormalizerInterface
{
    public function normalize(NormalizerContext $context);
}
