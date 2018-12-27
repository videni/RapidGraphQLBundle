<?php

namespace Videni\Bundle\RestBundle\Filter\Normalizer;

class NormalizerCompositor
{
    private $normalizers = [];

    public function __construct($normalizers = [])
    {
        $this->normalizers = $normalizers;
    }

    public function add($dataType, FilterValueNormalizerInterface  $normalizer)
    {
        $this->normalizers[$dataType] = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(NormalizerContext $context)
    {
       if (isset($this->normalizers[$context->getDataType()])) {
            $normalizer = $this->normalizers[$context->getDataType()];
            $normalizer->normalize($context);
       }
    }
}
