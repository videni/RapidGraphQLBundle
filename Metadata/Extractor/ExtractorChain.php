<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Metadata\Extractor;

class ExtractorChain implements ExtractorInterface
{
    private $extractors;

    public function __construct(array $extractors)
    {
        $this->extractors = $extractors;
    }

    public function addExtractor(ExtractorInterface $extractor)
    {
        $this->extractors[] = $extractor;
    }

    public function getResources(): array
    {
        $resources = [];

        foreach ($this->extractors as $extractor) {
            if (!$extractor instanceof ExtractorInterface) {
                throw new \RuntimeException(
                    sprintf(
                        'Extractor "%s" must be an instance of "ExtractorInterface" to use '.
                        '"ExtractorChain::getResources()".',
                        get_class($extractor)
                    )
                );
            }

            $extractorClasses = $extractor->getResources();
            if (!empty($extractorClasses)) {
                $resources = array_merge($resources, $extractorClasses);
            }
        }

        return $resources;
    }
}
