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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Videni\Bundle\RestBundle\Context\ResourceContextStorage;
use Videni\Bundle\RestBundle\Validator\Exception\ValidationException;
use Videni\Bundle\RestBundle\Context\ResourceContext;
use Videni\Bundle\RestBundle\Config\Resource\Resource;
use Videni\Bundle\RestBundle\Operation\ActionTypes;
use Videni\Bundle\RestBundle\Event\ResolveFormEvent;
use Limenius\Liform\Liform;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Videni\Bundle\RestBundle\Serializer\UiSchema;

final class FormListener
{
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
        if(!in_array($context->getActionType(), [ActionTypes::UPDATE, ActionTypes::CREATE])) {
            return;
        }

        $request = $event->getRequest();
        if ($request->attributes->get('_disable_form', false)) {
            return;
        }

        $data = $request->attributes->get('data');

        $form = $this->resolveForm($context, $data);

        $resolveFormEvent = new ResolveFormEvent($form, $data, $context, $request);

        $this->eventDispatcher->dispatch(ResolveFormEvent::AFTER_RESOLVE, $resolveFormEvent);
        if ($resolveFormEvent->getResponse()) {
            $event->setResponse($resolveFormEvent->getResponse());

            return;
        }

        $response = $this->processForm($request, $form);
        if ($response) {
            $event->setResponse($response);
        }

        //pass form to controller
        $request->attributes->set('form', $form);
    }

    protected function resolveForm(ResourceContext $context, $data)
    {
        $operationConfig = $context->getOperation();

        $formType = $context->getAction()->getForm();
        if (null === $formType) {
            throw new \LogicException(
                sprintf('The form is required for action %s of operation %s', $context->getActionName(), $context->getOperationName())
            );
        }

        $options = [
            'validation_groups' => $operationConfig->getActionAttribute($context->getActionName(), 'validation_groups', true),
            'data_class' => $this->getFormDataClass($context, $data),
            'csrf_protection' => false,
        ];

        return $this->formFactory->create($formType, $data, $options);
    }

    protected function processForm(Request $request, FormInterface $form)
    {
        $context = new SerializationContext();
        $context->setAttribute('form', $form);
        $context->setAttribute('form_schema_on_validation_error', $request->query->get('_return_schema_on_error', false));

        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true)) {
            /**
             * always use $clearMissing = false
             */
            $isValid = $form->submit($this->prepareRequestData($request->request->all()), false)->isValid();
            if (false === $isValid) {
                $context->setAttribute('status_code', Response::HTTP_BAD_REQUEST);

                return $this->createResponse($request, $form, Response::HTTP_BAD_REQUEST, $context);
            }
        }
        //serialize form and its initial values
        else {
            return $this->createResponse($request, $this->createFormSchema($form), Response::HTTP_OK, $context);
        }

        return null;
    }

    protected function createFormSchema(FormInterface $form)
    {
        $schema = $this->liform->transform($form);
        $uiSchema = UiSchema::extract($schema);

        return [
            'formData' => $form->createView(),
            'schema' => $schema,
            'uiSchema' => $uiSchema,
        ];
    }



    /**
     * @param FormContext            $context
     *
     * @return string
     */
    protected function getFormDataClass(ResourceContext $context, $entity)
    {
        $dataClass = $context->getResource()->getEntityClass();
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

    protected function createResponse(Request $request, $data, $status, SerializationContext $context = null)
    {
        $format = $request->getRequestFormat();

        return new Response(
            $this->serializer->serialize($data, $format, $context),
            $status,
            [
                'Content-Type' => sprintf('%s; charset=utf-8', $request->getMimeType($format)),
                'Vary' => 'Accept',
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'deny',
            ]
        );
    }
}
