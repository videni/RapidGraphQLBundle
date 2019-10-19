<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Provider\ResourceProvider;

use Videni\Bundle\RestBundle\Context\ResourceContext;
use Zend\Stdlib\PriorityQueue;

class ChainResourceProvider
{
    private $providers;

    /**
     * @param array $providers List of instances of ProcessorInterface.
     */
    public function __construct($providers = array())
    {
        $this->providers = new PriorityQueue();

        foreach ($providers as $provider) {
            $this->add($provider);
        }
    }

    public function add(ResourceProviderInterface $orderProcessor, int $priority = 0): void
    {
        $this->providers->insert($orderProcessor, $priority);
    }

    public function getResource(ResourceContext $context, callable $getter)
    {
        foreach($this->providers as $provider) {
            if (!$provider->supports($context)) {
                continue;
            }
            $resource = $provider->getResource($context, $getter);
            if(null !== $resource) {
                return $resource;
            }
        }

        return null;
    }
}
