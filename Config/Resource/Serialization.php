<?php

namespace Videni\Bundle\RestBundle\Config\Resource;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

class Serialization extends \ArrayObject
{
    /**
     * @var SerializationGroup
     */
    private $serializationGroup;

    private $properties = [];

    private $enableMaxDepth = null;

    private $section = null;

    private $version = null;

    public function __construct(array $param = [])
    {
        $this->serializationGroup = new SerializationGroup($param);
    }

    public function addGroup($group)
    {
        $this->serializationGroup[] = $group;
    }

    public function removeGroup($group)
    {
        unset($this->serializationGroup[$group]);
    }

    public function addGroupByPath($path, $group)
    {
        $existedGroup = $this->serializationGroup->offsetGetByPath($path, []);

        $this->serializationGroup->offsetSetByPath(
            $path,
            array_merge($existedGroup,  is_array($group)? $group : [$group])
        );

        return this;
    }

    public function removeGroupByPath($path)
    {
        $this->serializationGroup->offsetUnsetByPath($path);

        return this;
    }

    public function getGroups()
    {
        return $this->serializationGroup->toArray();
    }

    public static function fromArray($config = [])
    {
        $initialGroups = [];

        if (isset($config['groups'])) {
           $initialGroups = $config['groups'];
        }

        $self = new self($initialGroups);

        if (isset($config['enable_max_depth'])) {
            $self->setEnableMaxDepth($config['enable_max_depth']);
        }
        if (isset($config['section'])) {
            $self->setSection($config['section']);
        }
        if (isset($config['version'])) {
            $self->setVersion($config['version']);
        }

        return $self;
    }

    public function toArray()
    {
        return [
            'groups' => $this->getGroups(),
            'enable_max_depth' => $this->getEnableMaxDepth(),
            'section' => $this->getSection(),
        ];
    }

    /**
     * @return mixed
     */
    public function getEnableMaxDepth()
    {
        return $this->enableMaxDepth;
    }

    /**
     * @param mixed $enableMaxDepth
     *
     * @return self
     */
    public function setEnableMaxDepth($enableMaxDepth)
    {
        $this->enableMaxDepth = $enableMaxDepth;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param mixed $section
     *
     * @return self
     */
    public function setSection($section)
    {
        $this->section = $section;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     *
     * @return self
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }
}
