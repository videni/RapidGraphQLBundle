<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Factory;

interface ResourceFactoryInterface
{
    /**
     * @param ResourceMetadata $resourceMetadata
     * @param FactoryInterface $factory
     *
     * @return ResourceInterface
     */
    public function create(Request $request, array $factoryConfigurations);
}
