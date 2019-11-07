<?php

namespace Videni\Bundle\RapidGraphQLBundle\Controller;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Container;

class ContainerControllerResolver extends ControllerResolver
{
    protected $container;

    public function __construct(ContainerInterface $container, LoggerInterface $logger = null)
    {
        $this->container = $container;

        parent::__construct($logger);
    }

    protected function createController($controller)
    {
        if (1 === substr_count($controller, ':')) {
            $controller = str_replace(':', '::', $controller);
            // TODO deprecate this in 5.1
        }

        return parent::createController($controller);
    }

    /**
     * {@inheritdoc}
     */
    protected function instantiateController($class)
    {
        $class = ltrim($class, '\\');

        if ($this->container->has($class)) {
            return $this->configureController($this->container->get($class), $class);
        }

        try {
            return $this->configureController(parent::instantiateController($class), $class);
        } catch (\Error $e) {
        }

        $this->throwExceptionIfControllerWasRemoved($class, $e);

        if ($e instanceof \ArgumentCountError) {
            throw new \InvalidArgumentException(sprintf('Controller "%s" has required constructor arguments and does not exist in the container. Did you forget to define such a service?', $class), 0, $e);
        }

        throw new \InvalidArgumentException(sprintf('Controller "%s" does neither exist as service nor as class', $class), 0, $e);
    }

    private function configureController($controller, string $class)
    {
        if ($controller instanceof ContainerAwareInterface) {
            $controller->setContainer($this->container);
        }
        if ($controller instanceof AbstractController) {
            if (null === $previousContainer = $controller->setContainer($this->container)) {
                @trigger_error(sprintf('Auto-injection of the container for "%s" is deprecated since Symfony 4.2. Configure it as a service instead.', $class), E_USER_DEPRECATED);
            // To be uncommented on Symfony 5:
                //throw new \LogicException(sprintf('"%s" has no container set, did you forget to define it as a service subscriber?', $class));
            } else {
                $controller->setContainer($previousContainer);
            }
        }

        return $controller;
    }

    private function throwExceptionIfControllerWasRemoved(string $controller, \Throwable $previous)
    {
        if ($this->container instanceof Container && isset($this->container->getRemovedIds()[$controller])) {
            throw new \InvalidArgumentException(sprintf('Controller "%s" cannot be fetched from the container because it is private. Did you forget to tag the service with "controller.service_arguments"?', $controller), 0, $previous);
        }
    }
}
