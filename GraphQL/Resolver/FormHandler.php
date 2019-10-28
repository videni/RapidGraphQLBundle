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
use Limenius\Liform\Liform;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Overblog\GraphQLBundle\Validator\Exception\ArgumentsValidationException;
use Symfony\Component\Validator\ConstraintViolationList;
use Videni\Bundle\RapidGraphQLBundle\Form\FormSchemaTrait;

final class FormHandler
{
    use FormSchemaTrait {
        FormSchemaTrait::__construct as private formSchemaTraitConstructor;
    }

    private $validator;
    private $eventDispatcher;
    private $serializer;
    private $formFactory;

    public function __construct(
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        EventDispatcherInterface $eventDispatcher,
        Liform $liform
    ) {
        $this->formSchemaTraitConstructor($liform);

        $this->formFactory = $formFactory;
        $this->validator = $validator;
        $this->serializer = $serializer;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle($data, ResourceContext $context, array $input, Request $request)
    {
        $form = $this->resolveForm($context, $data, $request);

        return $this->processForm($request, $input,  $form);
    }

    public function generateFormSchema($data, ResourceContext $context, Request $request)
    {
        $form = $this->resolveForm($context, $data, $request);

        $context = new SerializationContext();
        $context
            ->setAttribute('form', $form)
            ->setAttribute('extra_context', new \ArrayObject());

        //serialize form and its initial values
        return $this->serializer->serialize($this->createFormSchema($form) , 'json', $context);
    }

    protected function resolveForm(ResourceContext $context, $data, Request $request)
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

    protected function processForm(Request $request, $input, FormInterface $form)
    {
        /**
         * always use $clearMissing = false
         */
        $isValid = $form->submit($this->prepareRequestData($input, $request), false)->isValid();
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

    /**
     * @param array $request
     *
     * @return array
     */
    protected function prepareRequestData(array $input, Request $request)
    {
        /**
         * as Symfony Form treats false as NULL due to checkboxes
         * @see \Symfony\Component\Form\Form::submit
         * we have to convert false to its string representation here
         */
        \array_walk_recursive(
            $input,
            function (&$value) {
                if (false === $value) {
                    $value = 'false';
                }
            }
        );

        return array_replace_recursive($input, $request->files->all());
    }
}
