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
use Overblog\GraphQLBundle\Validator\Exception\ArgumentsValidationException;
use Symfony\Component\Validator\ConstraintViolationList;

final class FormHandler
{
    private $validator;
    private $eventDispatcher;
    private $serializer;
    private $formFactory;

    public function __construct(
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher
    ) {

        $this->formFactory = $formFactory;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle($data, ResourceContext $context, array $input, Request $request)
    {
        $form = $this->resolveForm($context, $data, $request);

        return $this->processForm($input,  $form);
    }

    public function resolveForm(ResourceContext $context, $data, Request $request)
    {
        $resolveFormEvent = new ResolveFormEvent($data, $context, $request);

        $this->eventDispatcher->dispatch(ResolveFormEvent::BEFORE_RESOLVE, $resolveFormEvent);

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
            'allow_extra_fields' => true,
        ];

        $form = $this->formFactory->create($formType, $data, $options);

        $resolveFormEvent->setForm($form);

        $this->eventDispatcher->dispatch(ResolveFormEvent::AFTER_RESOLVE, $resolveFormEvent);

        //pass form to controller
        $request->attributes->set('form', $form);

        return $form;
    }

    protected function processForm($input, FormInterface $form)
    {
        /**
         * always use $clearMissing = false
         */
        $isValid = $form->submit($input, false)->isValid();
        if (false === $isValid) {
            $violations = [];
            foreach($form->getErrors(true) as $error) {
                $violations[] = $error->getCause();
            }

            throw new ArgumentsValidationException(new ConstraintViolationList($violations));
        }

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
