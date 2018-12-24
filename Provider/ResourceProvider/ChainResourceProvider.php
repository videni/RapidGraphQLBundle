<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Provider\ResourceProvider;

use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RestBundle\Context\ResourceContext;
use Zend\Stdlib\PriorityQueue;

class ChainResourceProvider implements ResourceProviderInterface
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

    public function get(ResourceContext $context, Request $request)
    {
        foreach($this->providers as $provider) {
            $data = $provider->get($context, $request);
            if(null !== $data) {
                return $data;
            }
        }

        return null;
    }
}
