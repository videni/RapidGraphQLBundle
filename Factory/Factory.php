<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Factory;

class Factory implements FactoryInterface
{
   /**
     * @var string
     */
    private $className;

    /**
     * @param string $className
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function createNew()
    {
        return new $this->className();
    }
}
