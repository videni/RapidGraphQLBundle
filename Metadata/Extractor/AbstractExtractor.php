<?php


declare(strict_types=1);

namespace App\Bundle\RestBundle\Metadata\Extractor;

abstract class AbstractExtractor implements ExtractorInterface
{
    /**
     * Extracts metadata from a given path.
     */
    abstract protected function extractPath($path);

     /**
     * Returns the extension of the file.
     *
     * @return string
     */
    abstract protected function getExtension();
}
