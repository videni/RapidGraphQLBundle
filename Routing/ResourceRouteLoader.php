<?php


declare(strict_types=1);

namespace App\Bundle\RestBundle\Routing;

use App\Bundle\RestBundle\Exception\InvalidResourceException;
use App\Bundle\RestBundle\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use App\Bundle\RestBundle\Metadata\Resource\ResourceMetadata;
use App\Bundle\RestBundle\Operation\OperationType;
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

/**
 * Loads Resources.
 */
final class ResourceRouteLoader extends Loader
{
    const ROUTE_NAME_PREFIX = 'api_';

    private $resourceMetadataFactory;
    private $operationPathResolver;
    private $resourceClassDirectories;
    private $resourceControllerId;

    public function __construct(
        ResourceMetadataFactoryInterface $resourceMetadataFactory,
        OperationPathResolverInterface $operationPathResolver,
        string $resourceControllerId,
        array $resourceClassDirectories = []
    ) {
        $this->resourceMetadataFactory = $resourceMetadataFactory;
        $this->operationPathResolver = $operationPathResolver;
        $this->resourceControllerId = $resourceControllerId;
        $this->resourceClassDirectories = $resourceClassDirectories;
    }

    /**
     * {@inheritdoc}
     */
    public function load($data, $type = null): RouteCollection
    {
        $routeCollection = new RouteCollection();
        foreach ($this->resourceClassDirectories as $directory) {
            $routeCollection->addResource(new DirectoryResource($directory, '/\.php$/'));
        }

        foreach ($this->resourceMetadataFactory->getAllResourceMetadatas() as $resourceClass => $resourceMetadata) {
            $resourceShortName = $resourceMetadata->getShortName();

            if (null === $resourceShortName) {
                throw new InvalidResourceException(sprintf('Resource %s has no short name defined.', $resourceClass));
            }

            if (null !== $operations = $resourceMetadata->getOperations()) {
                foreach ($operations as $operationName => $operation) {
                    $this->addRoute($routeCollection, $resourceClass, $operationName, $operation, $resourceMetadata);
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
    private function addRoute(RouteCollection $routeCollection, string $resourceClass, string $operationName, array $operation, ResourceMetadata $resourceMetadata)
    {
        $resourceShortName = $resourceMetadata->getShortName();

        if (isset($operation['route_name'])) {
            return;
        }

        if (!isset($operation['action'])) {
            throw new \RuntimeException(sprintf('Either a "route_name" or a "action" operation attribute must exist for the operation "%s" of the resource "%s".', $operationName, $resourceClass));
        }

        $action = $operation['action'];
        if (!ActionTypes::isSupport($action)) {
            throw new \RuntimeException(sprintf('%s is not a valid action', $action));
        }

        if (null === $controller = $operation['controller'] ?? null) {
            $controller = sprintf('%s:%s', $this->resourceControllerId, $action);
        }

        $path = trim(trim($resourceMetadata->getAttribute('route_prefix', '')), '/');
        $path .= $this->operationPathResolver->resolveOperationPath($resourceShortName, $operation, $operationName);

        $defaultMethods = ActionTypes::getMethods($operation['action']);

        $route = new Route(
            $path,
            [
                '_controller' => $controller,
                '_format' => null,
                '_api_resource_class' => $resourceClass,
                '_api_operation_name' => $operationName,
                '_action' => $action,
            ] + ($operation['defaults'] ?? []),
            $operation['requirements'] ?? [],
            $operation['options'] ?? [],
            $operation['host'] ?? '',
            $operation['schemes'] ?? [],
            !isset($operation['methods']) ? $defaultMethods : array_merge($defaultMethods, $operation['methods']),
            $operation['condition'] ?? ''
        );

        $routeCollection->add(RouteNameGenerator::generate($operationName, $resourceShortName), $route);
    }
}
