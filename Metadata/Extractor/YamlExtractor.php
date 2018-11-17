<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Metadata\Extractor;

use App\Bundle\RestBundle\Exception\InvalidArgumentException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use App\Bundle\RestBundle\Metadata\Resource\ResourceMetadata;

/**
 * Extracts an array of metadata from a list of YAML files.
 *
 */
final class YamlExtractor extends AbstractExtractor
{
    private $dirs;

    public function __construct(array $dirs)
    {
        $this->dirs = $dirs;
    }

    public function getResources(): array
    {
        $resources = [];
        $files = $this->findAllFiles($this->getExtension());
        foreach ($files as $fileName => $file) {
            $resources = array_merge($resources, $this->extractPath($file));
        }

        return $resources;
    }

    public function getDirs()
    {
        return $this->dirs;
    }

    protected function getExtension()
    {
        return 'yaml';
    }

    /**
     * {@inheritdoc}
     */
    protected function extractPath($path)
    {
        try {
            $resourcesYaml = Yaml::parse(file_get_contents($path), Yaml::PARSE_CONSTANT);
        } catch (ParseException $e) {
            $e->setParsedFile($path);

            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }

        if (null === $resourcesYaml = $resourcesYaml['resources'] ?? $resourcesYaml) {
            return;
        }

        if (!\is_array($resourcesYaml)) {
            throw new InvalidArgumentException(sprintf('"resources" setting is expected to be null or an array, %s given in "%s".', \gettype($resourcesYaml), $path));
        }

        $resources =  [];
        foreach ($resourcesYaml as $resourceName => $resourceYaml) {
            if (null === $resourceYaml) {
                continue;
            }

            $resources[$resourceName] = new ResourceMetadata(
                $this->phpize($resourceYaml, 'shortName', 'string')?? $this->getClassName($resourceName),
                $this->phpize($resourceYaml, 'description', 'string'),
                $resourceYaml['itemOperations'] ?? null,
                $resourceYaml['collectionOperations'] ?? null,
                $resourceYaml['attributes'] ?? null
            );
        }

        return $resources;
    }

    private function getClassName($resourceName)
    {
        if (false !== $pos = strrpos($resourceName, '\\')) {
            return substr($resourceName, $pos + 1);
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function findAllFiles($extension)
    {
        $files = array();


        foreach ($this->dirs as $dir) {
            /** @var $iterator \RecursiveIteratorIterator|\SplFileInfo[] */
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($iterator as $file) {
                if (($fileName = $file->getBasename('.'.$extension)) == $file->getBasename()) {
                    continue;
                }

                $files[$fileName] =  $file->getRealPath();
            }
        }

        return $files;
    }

    /**
     * Transforms a YAML attribute's value in PHP value.
     *
     *
     * @throws InvalidArgumentException
     *
     * @return bool|string|null
     */
    private function phpize(array $array, string $key, string $type)
    {
        if (!isset($array[$key])) {
            return null;
        }

        switch ($type) {
            case 'bool':
                if (\is_bool($array[$key])) {
                    return $array[$key];
                }
                break;
            case 'string':
                if (\is_string($array[$key])) {
                    return $array[$key];
                }
                break;
        }

        throw new InvalidArgumentException(sprintf('The property "%s" must be a "%s", "%s" given.', $key, $type, \gettype($array[$key])));
    }
}
