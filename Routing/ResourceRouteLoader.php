<?php


declare(strict_types=1);

namespace App\Bundle\RestBundle\Routing;

use App\Bundle\RestBundle\Exception\InvalidResourceException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use App\Bundle\RestBundle\Routing\PathResolver\OperationPathResolverInterface;
use App\Bundle\RestBundle\Operation\ActionTypes;
use App\Bundle\RestBundle\Config\Resource\ResourceConfigProvider;
use App\Bundle\RestBundle\Config\Resource\ResourceConfig;
use App\Bundle\RestBundle\Config\Resource\OperationConfig;

/**
 * Loads Resources.
 */
final class ResourceRouteLoader extends Loader
{
    const ROUTE_NAME_PREFIX = 'api_';

    private $resourceConfigProvider;
    private $operationPathResolver;
    private $resourceControllerId;

    public function __construct(
        ResourceConfigProvider $resourceConfigProvider,
        OperationPathResolverInterface $operationPathResolver,
        string $resourceControllerId
    ) {
        $this->resourceConfigProvider = $resourceConfigProvider;
        $this->operationPathResolver = $operationPathResolver;
        $this->resourceControllerId = $resourceControllerId;
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
    private function addRoute(RouteCollection $routeCollection, string $resourceClass, string $operationName, OperationConfig $operationConfig, ResourceConfig $resourceConfig)
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

        $controller = $operationConfig->getController() ?? sprintf('%s:%s', $this->resourceControllerId, $action);

        $path = trim(trim($resourceConfig->getRoutePrefix()), '/');
        $path .= $this->operationPathResolver->resolveOperationPath($resourceShortName, $operationConfig, $operationName);

        $defaultMethods = ActionTypes::getMethods($action);

        $route = new Route(
            $path,
            [
                '_controller' => $controller,
                '_format' => null,
                '_api_resource_class' => $resourceClass,
                '_api_operation_name' => $operationName,
                '_action' => $action,
            ] + $operationConfig->getDefaults(),
            [],
            [],
            '',
            [],
            $operationConfig->getMethods() ?? array_merge($defaultMethods, $operationConfig->getMethods())
        );

        $routeCollection->add(RouteNameGenerator::generate($operationName, $resourceShortName), $route);
    }
}
