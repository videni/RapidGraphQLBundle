<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Videni\Bundle\RestBundle\Processor\Context;
use Videni\Bundle\RestBundle\Processor\ContextFactory;
use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RestBundle\Processor\ActionProcessorBagInterface;
use Videni\Bundle\RestBundle\Request\RestRequestHeaders;
use Videni\Bundle\RestBundle\Operation\ActionTypes;
use Videni\Bundle\RestBundle\Processor\SerializerFormat;
use FOS\RestBundle\View\View;
use Videni\Bundle\RestBundle\Handler\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Videni\Bundle\RestBundle\Filter\FilterValue\FilterValueAccessorInterface;
use Videni\Bundle\RestBundle\Filter\FilterValue\FilterValueAccessorFactory;

class ResourceController extends Controller
{
    private $serializerFormat;
    private $actionProcessorBag;
    private $viewHandler;
    private $filterValueAccessorFactory;

    public function __construct(
        ActionProcessorBagInterface $actionProcessorBag,
        ViewHandlerInterface $viewHandler,
        SerializerFormat $serializerFormat,
        FilterValueAccessorFactory $filterValueAccessorFactory
    ) {
        $this->actionProcessorBag = $actionProcessorBag;
        $this->serializerFormat = $serializerFormat;
        $this->viewHandler = $viewHandler;
        $this->filterValueAccessorFactory = $filterValueAccessorFactory;
    }

    /**
     * Get a list of entities
     *
     * @param Request $request
     *
     * @return Context
     */
    public function index(Request $request)
    {
        $processor = $this->getProcessor(ActionTypes::INDEX);

        /** @var IndexContext $context */
        $context = $processor->createContext();
        $this->prepareContext($context, $request);
        $context->setFilterValues($this->getRequestFilters($request));

        $processor->process($context);

        return $this->buildResponse($context);
    }

    /**
     * Get an entity
     *
     * @param Request $request
     *
     * @return Context
     */
    public function view(Request $request)
    {
        $processor = $this->getProcessor(ActionTypes::VIEW);

        /** @var ViewContext $context */
        $context = $processor->createContext();

        $this->prepareContext($context, $request);
        $processor->process($context);

        $this->throwNotFoundHttpException($context);

        return $this->buildResponse($context);
    }

    /**
     * Delete an entity
     *
     * @param Request $request
     *
     * @return Context
     */
    public function delete(Request $request)
    {
         $processor = $this->getProcessor(ActionTypes::DELETE);

        /** @var DeleteContext $context */
        $context = $processor->createContext();

        $this->prepareContext($context, $request);

        $processor->process($context);

        return $this->buildResponse($context);
    }

    /**
     * Delete a list of entities
     *
     * @param Request $request
     *
     * @return Context
     */
    public function bulkDelete(Request $request)
    {
        $processor = $this->getProcessor(ActionTypes::BULK_DELETE);

        /** @var DeleteListContext $context */
        $context = $processor->createContext();

        $this->prepareContext($context, $request);
        $context->setFilterValues($this->getRequestFilters($request));

        $processor->process($context);

        return $this->buildResponse($context);
    }

    /**
     * Update an entity
     *
     * @param Request $request
     *
     * @return Context
     */
    public function update(Request $request)
    {
        $processor = $this->getProcessor(ActionTypes::UPDATE);

        /** @var UpdateContext $context */
        $context = $processor->createContext();

        $this->prepareContext($context, $request);

        $processor->process($context);

        $this->throwNotFoundHttpException($context);

        return $this->buildResponse($context);
    }

    /**
     * Create an entity
     *
     * @param Request $request
     *
     * @return Context
     */
    public function create(Request $request)
    {
        $processor = $this->getProcessor(ActionTypes::CREATE);

        /** @var CreateContext $context */
        $context = $processor->createContext();

        $this->prepareContext($context, $request);

        $processor->process($context);

        return $this->buildResponse($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequestFilters(Request $request): FilterValueAccessorInterface
    {
        return $this->filterValueAccessorFactory->create($request);
    }

     /**
     * {@inheritdoc}
     */
    protected function buildResponse(Context $context): Response
    {
        $view = View::create($context->getResult());
        $view->setFormat($context->getFormat());

        $view->setStatusCode($context->getResponseStatusCode() ?: Response::HTTP_OK);
        foreach ($context->getResponseHeaders()->toArray() as $key => $value) {
            $view->setHeader($key, $value);
        }

        return $this->viewHandler->handle($context, $view);
    }

    private function prepareContext(Context $context, Request $request)
    {
        $context->setRequest($request);
        $context->setClassName($request->attributes->get('_api_resource_class'));
        $context->setOperationName($request->attributes->get('_api_operation_name'));
        $context->setRequestHeaders(new RestRequestHeaders($request));
        $context->setFormat($this->serializerFormat->getFormat($request, $context));

        $context->loadResourceConfig();
    }

         /**
     * @param Request $request
     *
     * @return ActionProcessorInterface
     */
    private function getProcessor($action)
    {
        return $this->actionProcessorBag->getProcessor($action);
    }

    private function throwNotFoundHttpException(Context $context)
    {
        if (null === $context->getResult()) {
            throw new NotFoundHttpException(sprintf('The "%s" has not been found', $context->getMetadata()->getShortName()));
        }
    }
}
