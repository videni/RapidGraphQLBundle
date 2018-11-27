<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Operation;

/**
 * Generates a path name according to a string and whether it's a collection or not.
 */
interface PathSegmentNameGeneratorInterface
{
    /**
     * Transforms a given string to a valid path name which can be pluralized (eg. for collections).
     *
     * @param string $name usually a resource shortname
     *
     * @return string A string that is a part of the route name
     */
    public function getSegmentName(string $name, bool $collection = true): string;
}
