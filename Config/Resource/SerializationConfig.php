<?php

namespace App\Bundle\RestBundle\Config\Resource;

class SerializationConfig
{
    private $groups;

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

        return $self;
    }
}
