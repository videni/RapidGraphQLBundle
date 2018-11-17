<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Metadata\Extractor;

use App\Bundle\RestBundle\Exception\InvalidArgumentException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use App\Bundle\RestBundle\Metadata\Resource\ResourceMetadata;
use App\Bundle\RestBundle\Annotation\Resource;
use App\Bundle\RestBundle\Exception\ResourceClassNotFoundException;
use Doctrine\Common\Annotations\Reader;

final class AnnotationExtractor implements ExtractorInterface
{
    private $classes;

    private $reader;

    public function __construct(Reader $reader, $classes = [])
    {
        $this->reader = $reader;
        $this->classes = $classes;
    }

    public function getClasses()
    {
        return $this->classes;
    }

    public function getResources(): array
    {
        $resources = [];

        foreach ($this->classes as $class) {
            $resources [] = $this->create($class);
        }

        return $resources;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $resourceClass): ResourceMetadata
    {
        try {
            $reflectionClass = new \ReflectionClass($resourceClass);
        } catch (\ReflectionException $reflectionException) {
            return $this->handleNotFound($parentResourceMetadata, $resourceClass);
        }

        $resourceAnnotation = $this->reader->getClassAnnotation($reflectionClass, Resource::class);
        if (null === $resourceAnnotation) {
            throw new ResourceClassNotFoundException(sprintf('Resource "%s" not found.', $resourceClass));
        }

        return new ResourceMetadata(
            $annotation->shortName,
            $annotation->description,
            $annotation->operations,
            $annotation->attributes
        );
        ;
    }
}
