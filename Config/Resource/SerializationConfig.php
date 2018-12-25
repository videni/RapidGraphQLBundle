<?php

namespace Videni\Bundle\RestBundle\Config\Resource;

class SerializationConfig extends \ArrayObject
{
    private $groups = null;

    private $enableMaxDepth = null;

    /**
     * @return mixed
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param mixed $groups
     *
     * @return self
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;

        return $this;
    }

    public static function fromArray($config = [])
    {
        $self = new self();

        if (isset($config['groups'])) {
            $self->setGroups($config['groups']);
        }
        if (isset($config['enable_max_depth'])) {
            $self->setEnableMaxDepth($config['enable_max_depth']);
        }

        return $self;
    }

    public function toArray()
    {
        return [
            'groups' => $this->getGroups()
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
}
