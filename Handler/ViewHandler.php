<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Handler;

use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use App\Bundle\RestBundle\Processor\Context as ProcessorContext;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\ViewHandlerInterface as RestViewHandlerInterface;
use App\Bundle\RestBundle\Config\Resource\ResourceConfig;

final class ViewHandler implements ViewHandlerInterface
{
    /**
     * @var RestViewHandler
     */
    private $restViewHandler;

    /**
     * @param RestViewHandler $restViewHandler
     */
    public function __construct(RestViewHandlerInterface $restViewHandler)
    {
        $this->restViewHandler = $restViewHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ProcessorContext $context, View $view): Response
    {
        $viewContext = $this->createContext($context->getClassName(), $context->getOperationName(), $context->getResourceConfig());
        $view->setContext($viewContext);

        return $this->restViewHandler->handle($view);
    }

    public function createContext($class, $operationName, ResourceConfig $resourceConfig)
    {
        $context = new Context();
        $context->setAttribute('api_operation_name', $operationName);
        if ($normailzationConfig = $resourceConfig->getOperationAttribute($operationName, 'normalization_context')) {
            $context->setGroups($normailzationConfig->getGroups());
        }

        $context->setAttribute('resource_class', $class);

        return $context;
    }
}
