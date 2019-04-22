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
use Videni\Bundle\RestBundle\Config\Resource\ConfigProvider;
use Videni\Bundle\RestBundle\Config\Resource\Resource;
use Videni\Bundle\RestBundle\Config\Resource\Operation;
use Videni\Bundle\RestBundle\Config\Resource\Action;
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
        ConfigProvider $resourceConfigProvider,
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

        foreach ($this->resourceConfigProvider->getAllOperations() as $operationName => $operationConfig) {
            if (null !== $actions = $operationConfig->getActions()) {
                foreach ($actions as $actionName => $actionConfig) {
                    $this->addRoute($routeCollection, $actionName, $operationName, $actionConfig, $operationConfig);
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
    private function addRoute(RouteCollection $routeCollection, string $actionName, string $operationName, Action $actionConfig, Operation $operationConfig)
    {
        if ($actionConfig->getRouteName()) {
            return;
        }
        if (!$actionConfig->getAction()) {
            throw new \RuntimeException(
                sprintf(
                    'Either a "route_name" or a "action" operation attribute must exist for the action "%s" of the operation "%s".',
                    $actionName,
                    $operationName
                )
            );
        }

        $action = $actionConfig->getAction();
        if (!ActionTypes::isSupport($action)) {
            throw new \RuntimeException(sprintf('%s is not a valid action', $action));
        }

        $controllerId = $controller = $actionConfig->getController() ?? sprintf('%s.%s', self::DEFAULT_ACTION_PATTERN, strtolower($action));
        if (strpos($controller, '::')) {
            list($controllerId, $method) = explode('::', $controller);
        }
        if (!$this->container->has($controllerId)) {
            throw new \RuntimeException(
                sprintf(
                    'There is no builtin action or controller defined for action %s of opeartion %s. You need to define the controller yourself.',
                    $actionName,
                    $operationName
                )
            );
        }

        $path = $operationConfig->getRoutePrefix() ? trim(trim($operationConfig->getRoutePrefix()), '/'): '/';
        $path .= $this->operationPathResolver->resolveOperationPath($operationName, $actionConfig, $actionName);

        $defaultMethods = ActionTypes::getMethods($action);

        $route = new Route(
            $path,
            [
                '_controller' => $controller,
                '_format' => null,
                '_action' => $action,
                '_api_operation_name' => $operationName,
                '_api_action_name' => $actionName,
            ] + $actionConfig->getDefaults(),
            $actionConfig->getRequirements() ?? [],
            [],
            '',
            [],
            empty($actionConfig->getMethods()) ? $defaultMethods: $actionConfig->getMethods()
        );

        $routeCollection->add(RouteNameGenerator::generate($actionName, $operationName), $route);
    }
}
