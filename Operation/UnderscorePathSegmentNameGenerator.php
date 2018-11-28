<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Operation;

use Doctrine\Common\Inflector\Inflector;

/**
 * Generate a path name with an underscore separator according to a string and whether it's a collection or not.
 */
final class UnderscorePathSegmentNameGenerator implements PathSegmentNameGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSegmentName(string $name, bool $collection = true): string
    {
        $name = Inflector::tableize($name);

        return $collection ? Inflector::pluralize($name) : $name;
    }
}
