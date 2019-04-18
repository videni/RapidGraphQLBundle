<?php


declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Routing;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Videni\Bundle\RestBundle\Routing\PathResolver\OperationPathResolverInterface;
use Videni\Bundle\RestBundle\Operation\ActionTypes;
use Videni\Bundle\RestBundle\Config\Resource\ResourceProvider;
use Videni\Bundle\RestBundle\Config\Resource\Resource;
use Videni\Bundle\RestBundle\Config\Resource\Operation;
use Videni\Bundle\RestBundle\Exception\InvalidResourceException;

/**
 * Loads Resources.
 */
final class ResourceRouteLoader extends Loader
{
    const ROUTE_NAME_PREFIX = 'api_';
    const DEFAULT_ACTION_PATTERN = 'videni_rest.action';

    private $resourceConfigProvider;
    private $operationPathResolver;
    private $container;

    public function __construct(
        ResourceProvider $resourceConfigProvider,
        OperationPathResolverInterface $operationPathResolver,
        ContainerInterface $container
    ) {
        $this->resourceConfigProvider = $resourceConfigProvider;
        $this->operationPathResolver = $operationPathResolver;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load($data, $type = null): RouteCollection
    {
        $routeCollection = new RouteCollection();

        foreach ($this->resourceConfigProvider->getAll() as $resourceClass => $resourceConfig) {
            $resourceShortName = $resourceConfig->getShortName();
            if (null !== $operations = $resourceConfig->getOperations()) {
                foreach ($operations as $operationName => $operationConfig) {
                    $this->addRoute($routeCollection, $resourceClass, $operationName, $operationConfig, $resourceConfig);
                }
            }
        }

        return $routeCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return 'videni_rest' === $type;
    }

    /**
     * Creates and adds a route for the given operation to the route collection.
     *
     * @throws RuntimeException
     */
    private function addRoute(RouteCollection $routeCollection, string $resourceClass, string $operationName, Operation $operationConfig, Resource $resourceConfig)
    {
        $resourceShortName = $resourceConfig->getShortName();

        if ($operationConfig->getRouteName()) {
            return;
        }
        if (!$operationConfig->getAction()) {
            throw new \RuntimeException(sprintf('Either a "route_name" or a "action" operation attribute must exist for the operation "%s" of the resource "%s".', $operationName, $resourceClass));
        }

        $action = $operationConfig->getAction();
        if (!ActionTypes::isSupport($action)) {
            throw new \RuntimeException(sprintf('%s is not a valid action', $action));
        }

        $controllerId = $controller = $operationConfig->getController() ?? sprintf('%s.%s', self::DEFAULT_ACTION_PATTERN, strtolower($action));
        if (strpos($controller, '::')) {
            list($controllerId, $method) = explode('::', $controller);
        }
        if (!$this->container->has($controllerId)) {
            throw new \RuntimeException(
                sprintf(
                    'There is no builtin action or controller defined for operation %s of resource %s. You need to define the controller yourself.',
                    $operationName,
                    $resourceShortName
                )
            );
        }

        $path = $resourceConfig->getRoutePrefix() ? trim(trim($resourceConfig->getRoutePrefix()), '/'): '/';
        $path .= $this->operationPathResolver->resolveOperationPath($resourceShortName, $operationConfig, $operationName);

        $defaultMethods = ActionTypes::getMethods($action);

        $route = new Route(
            $path,
            [
                '_controller' => $controller,
                '_format' => null,
                '_action' => $action,
                '_api_resource_class' => $resourceClass,
                '_api_operation_name' => $operationName,
            ] + $operationConfig->getDefaults(),
            $operationConfig->getRequirements() ?? [],
            [],
            '',
            [],
            empty($operationConfig->getMethods()) ? $defaultMethods: $operationConfig->getMethods()
        );

        $routeCollection->add(RouteNameGenerator::generate($operationName, $resourceShortName), $route);
    }
}
