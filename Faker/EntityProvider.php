<?php

namespace Videni\Bundle\RestBundle\Faker;

use Doctrine\Common\Persistence\ObjectManager;

class EntityProvider
{
    private $entityManger;

    public function __construct(ObjectManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function entity(string $class, $method, $value)
    {
        return $this->getEntity($class, $method, $value);
    }

    public function entityId(string $class, $method, $value)
    {
        return $this->getEntity($class, $method, $value)->getId();
    }

    protected function getEntity(string $class, $method, $value)
    {
        if (!class_exists($class, true)) {
            throw new \Exception(sprintf('Class %s is not existed', $class));
        }

        $repository = $this->entityManager->getRepository($class);
        if (!$repository) {
            throw new \Exception(sprintf('Class %s is not managed by doctrine', $class));
        }

        $result = call_user_func([$repository, $method], $value);
        if (!$result) {
            throw new \Exception(sprintf('No result found for class %s by method %s  via with value %s', $class, $method, $value));
        }

         return $result;
    }
}
