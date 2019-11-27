<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Videni\Bundle\RapidGraphQLBundle\Context\ResourceContext;
use Videni\Bundle\RapidGraphQLBundle\Event\ResolveFormEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Videni\Bundle\RapidGraphQLBundle\Config\Resource\Resource;
use Videni\Bundle\RapidGraphQLBundle\Config\Resource\Action;

final class FormHandler
{
    private $validator;
    private $eventDispatcher;
    private $formFactory;
    private $container;

    public function __construct(
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher,
        ContainerInterface $container
    ) {

        $this->formFactory = $formFactory;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
        $this->container = $container;
    }

    public function handle($data, ResourceContext $context, array $input, Request $request)
    {
        $form = $this->resolveForm($context, $data, $request);

        return $this->processForm($input, $form, $context->getAction());
    }

    public function resolveForm(ResourceContext $context, $data, Request $request)
    {
        $resolveFormEvent = new ResolveFormEvent($data, $context, $request);

        $this->eventDispatcher->dispatch(ResolveFormEvent::BEFORE_RESOLVE, $resolveFormEvent);

        $action = $context->getAction();
        $formType = $action->getFormClass();
        if (null === $formType) {
            throw new \LogicException(
                sprintf('The form is required for action %s of operation %s', $context->getActionName(), $context->getOperationName())
            );
        }

        $options = [
            'data_class' => $this->getFormDataClass($context, $data),
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ];

        if ($action->getFormValidationGroups()) {
            $options['validation_groups'] = $action->getFormValidationGroups();
        }

        $form = $this->formFactory->create($formType, $data, $options);

        $resolveFormEvent->setForm($form);

        $this->eventDispatcher->dispatch(ResolveFormEvent::AFTER_RESOLVE, $resolveFormEvent);

        //pass form to controller
        $request->attributes->set('form', $form);

        return $form;
    }

    protected function processForm($input, FormInterface $form, Action $action)
    {
        $formHandler = $action->getFormHandler() ? $this->container->get($action->getFormHandler()): null;
        /**
         * always use $clearMissing = false
         */
        $isValid = $form->submit($input, false)->isValid();
        if (false === $isValid) {
            $formHandler && $formHandler->onFailed($form);
        }

        $formHandler && $formHandler->onSuccess($form);

        return $form->getData();
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
}
