<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Form\FormInterface;
use Videni\Bundle\RestBundle\Context\ResourceContextStorage;
use Videni\Bundle\RestBundle\Validator\Exception\ValidationException;
use Videni\Bundle\RestBundle\Context\ResourceContext;
use Videni\Bundle\RestBundle\Config\Resource\ResourceConfig;
use Videni\Bundle\RestBundle\Operation\ActionTypes;
use Limenius\Liform\Liform;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Videni\Bundle\RestBundle\Event\AfterFormResolveEvent;

final class FormListener
{
    const AFTER_FORM_RESOLVE = 'videni.form_listener.after_form_resolve';

    private $formFactory;
    private $validator;
    private $resourceContextStorage;
    private $eventDispatcher;
    private $liform;

    public function __construct(
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        ResourceContextStorage $resourceContextStorage,
        SerializerInterface $serializer,
        EventDispatcherInterface $eventDispatcher,
        Liform $liform
    ) {
        $this->formFactory = $formFactory;
        $this->validator = $validator;
        $this->resourceContextStorage = $resourceContextStorage;
        $this->serializer = $serializer;
        $this->eventDispatcher = $eventDispatcher;
        $this->liform = $liform;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if(!$event->isMasterRequest()) {
            return;
        }

        $context = $this->resourceContextStorage->getContext();
        if (null == $context) {
            return;
        }
        if(!in_array($context->getAction(), [ActionTypes::UPDATE, ActionTypes::CREATE])) {
            return;
        }

        $request = $event->getRequest();
        if ($request->attributes->get('_disable_form', false)) {
            return;
        }

        $data = $request->attributes->get('data');

        $form = $this->resolveForm($context, $data);
        $afterFormResolveEvent = new AfterFormResolveEvent($form, $data, $context, $request);
        $this->eventDispatcher->dispatch(self::AFTER_FORM_RESOLVE, $afterFormResolveEvent);
        if ($afterFormResolveEvent->getResponse()) {
            $event->setResponse($afterFormResolveEvent->getResponse());

            return;
        }

        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true)) {
            /**
             * always use $clearMissing = false
             */
            $isValid = $form->submit($this->prepareRequestData($request->request->all()), false)->isValid();
            if (false === $isValid) {
                $data = $this->serializer->normalize($form, null , ['status_code' => Response::HTTP_BAD_REQUEST]) + [
                    'initial_values' => $form->createView(),
                    'form_schema' => $this->liform->transform($form),
                ];

                $this->setResponse($event, $data, Response::HTTP_BAD_REQUEST);
            }
        }
        //serialize form and its initial values
        else {
            $data = [
                'initial_values' => $form->createView(),
                'form_schema' => $this->liform->transform($form)
            ];

            $this->setResponse($event, $data, Response::HTTP_OK);
        }
    }

    protected function setResponse($event, $data, $status)
    {
        $request = $event->getRequest();

        $event->setResponse(new Response(
            $this->serializer->serialize($data, $request->getRequestFormat()),
            $status,
            [
                'Content-Type' => sprintf('%s; charset=utf-8', $request->getMimeType($request->getRequestFormat())),
                'Vary' => 'Accept',
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'deny',
            ]
        ));
    }

    protected function resolveForm(ResourceContext $context, $data)
    {
        $resourceConfig = $context->getResourceConfig();

        $formType = $resourceConfig->getOperationAttribute($context->getOperationName(), 'form', true);
        if (null === $formType) {
            return new \LogicException(sprintf('The form is required for operation %s of resource %s', $context->getOperationName(), $context->getClassName()));
        }

        $options = [
            'validation_groups' => $resourceConfig->getOperationAttribute($context->getOperationName(), 'validation_groups', true),
            'data_class' =>  $this->getFormDataClass($context, $data),
            'csrf_protection' => false,
        ];

        return $this->formFactory->create($formType, $data, $options);
    }

    /**
     * @param FormContext            $context
     *
     * @return string
     */
    protected function getFormDataClass(ResourceContext $context, $entity)
    {
        $dataClass = $context->getClassName();
        if (\is_object($entity)) {
            $entityClass = ClassUtils::getClass($entity);
            if ($entityClass !== $dataClass) {
                $dataClass = $entityClass;
            }
        }

        return $dataClass;
    }

    /**
     * @param array $requestData
     *
     * @return array
     */
    protected function prepareRequestData(array $requestData)
    {
        /**
         * as Symfony Form treats false as NULL due to checkboxes
         * @see \Symfony\Component\Form\Form::submit
         * we have to convert false to its string representation here
         */
        \array_walk_recursive(
            $requestData,
            function (&$value) {
                if (false === $value) {
                    $value = 'false';
                }
            }
        );

        return $requestData;
    }
}
