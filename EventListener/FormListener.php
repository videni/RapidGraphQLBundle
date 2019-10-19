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
use Videni\Bundle\RestBundle\Form\FormSchemaTrait;

final class FormListener
{
    use FormSchemaTrait {
        FormSchemaTrait::__construct as private formSchemaTraitConstructor;
    }

    private $formFactory;
    private $validator;
    private $resourceContextStorage;
    private $eventDispatcher;

    public function __construct(
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        ResourceContextStorage $resourceContextStorage,
        SerializerInterface $serializer,
        EventDispatcherInterface $eventDispatcher,
        Liform $liform
    ) {
        $this->formSchemaTraitConstructor($liform);

        $this->formFactory = $formFactory;
        $this->validator = $validator;
        $this->resourceContextStorage = $resourceContextStorage;
        $this->serializer = $serializer;
        $this->eventDispatcher = $eventDispatcher;
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
        $resolveFormEvent = new ResolveFormEvent($data, $context, $request);

        $this->eventDispatcher->dispatch(ResolveFormEvent::BEFORE_RESOLVE, $resolveFormEvent);

        $form = $this->resolveForm($context, $data);
        //pass form to controller
        $request->attributes->set('form', $form);
        $resolveFormEvent->setForm($form);

        $this->eventDispatcher->dispatch(ResolveFormEvent::AFTER_RESOLVE, $resolveFormEvent);
        if ($resolveFormEvent->getResponse()) {
            $event->setResponse($resolveFormEvent->getResponse());

            return;
        }

        $response = $this->processForm($request, $form);
        if ($response) {
            $event->setResponse($response);
        }
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
        $context
            ->setAttribute('form', $form)
            ->setAttribute('root_entity', $request->attributes->get('data'))
            ->setAttribute('extra_context', new \ArrayObject())
            ->setAttribute('form_schema_on_validation_error', $request->query->get('_return_schema_on_error', false));

        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true)) {
            /**
             * always use $clearMissing = false
             */
            $isValid = $form->submit($this->prepareRequestData($request), false)->isValid();
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
     * @param array $request
     *
     * @return array
     */
    protected function prepareRequestData(Request $request)
    {
        $params = $request->request->all();

        /**
         * as Symfony Form treats false as NULL due to checkboxes
         * @see \Symfony\Component\Form\Form::submit
         * we have to convert false to its string representation here
         */
        \array_walk_recursive(
            $params,
            function (&$value) {
                if (false === $value) {
                    $value = 'false';
                }
            }
        );

        return array_replace_recursive($params, $request->files->all());
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
