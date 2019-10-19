<?php

namespace Videni\Bundle\RestBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormInterface;
use Videni\Bundle\RestBundle\Context\ResourceContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

final class ResolveFormEvent extends Event
{
    const BEFORE_RESOLVE = 'videni_rest.resolve_form.before';
    const AFTER_RESOLVE  = 'videni_rest.resolve_form.after';

    protected $context;

    protected $data;

    protected $response;

    protected $request;

    /**
     * @var FormInterface
     */
    protected $form;

    public function __construct($data, ResourceContext $context, Request $request)
    {
        $this->data = $data;
        $this->context = $context;
        $this->request = $request;
    }

    /**
     * @return mixed
     */
    public function getContext(): ResourceContext
    {
        return $this->context;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return FormInterface
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    /**
     * @param mixed $response
     *
     * @return self
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * @return mixed
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param  FormInterface  $form
     *
     * @return  self
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;

        return $this;
    }
}
