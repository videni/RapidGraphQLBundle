<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Bundle\RestBundle\Processor\Context;
use App\Bundle\RestBundle\Processor\ContextFactory;

class ResourceController extends Controller
{

    private $contextFactory;

    public function __construct(ContextFactory $contextFactory)
    {
        $this->contextFactory = $contextFactory;
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
        /** @var GetListContext $context */
        $context = $this->contextFactory->create($processor, $request);
        $context->setFilterValues(new RestFilterValueAccessor($request));

        $processor->process($context);

        return $context;
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
        /** @var GetContext $context */
        $context = $this->contextFactory->create($processor, $request);
        $context->setId($request->attributes->get('id'));
        $context->setFilterValues(new RestFilterValueAccessor($request));

        $processor->process($context);

        return $context;
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
        /** @var DeleteContext $context */
        $context = $this->contextFactory->create($processor, $request);
        $context->setId($request->attributes->get('id'));

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
    public function deleteBatch(Request $request)
    {
        /** @var DeleteListContext $context */
        $context = $this->contextFactory->create($processor, $request);
        $context->setFilterValues(new RestFilterValueAccessor($request));

        $processor->process($context);

        return $context;
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
        /** @var UpdateContext $context */
        $context = $this->contextFactory->create($processor, $request);
        $context->setId($request->attributes->get('id'));
        $context->setRequestData($request->request->all());

        $processor->process($context);

        return $context;
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
        /** @var CreateContext $context */
        $context = $this->contextFactory->create($processor, $request);
        $context->setRequestData($request->request->all());

        $processor->process($context);


        return $context;
    }
}
