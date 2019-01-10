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

final class FormListener
{
    private $formFactory;
    private $validator;
    private $resourceContextStorage;

    public function __construct(
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        ResourceContextStorage $resourceContextStorage
    ) {
        $this->formFactory = $formFactory;
        $this->validator = $validator;
        $this->resourceContextStorage = $resourceContextStorage;
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
        if($request->attributes->get('_disable_form', false)) {
            return;
        }

        $form = $this->createForm($context, $request->attributes->get('data'));
        if ($form) {
             /**
             * always use $clearMissing = false, more details in:
             * @see \VideniBundleRestBundle\Form\FormValidationHandler::validate
             * @see \VideniBundleRestBundle\Processor\Shared\BuildFormBuilder::$enableFullValidation
             */
            $form->submit($this->prepareRequestData($request->request->all()), false);

            $violations = $this->validate($form);
            if (0 !== \count($violations)) {
                throw new ValidationException($violations);
            }
        }
    }

    /**
     * @param FormContext $context
     */
    protected function createForm(ResourceContext $context, $data)
    {
        $resourceConfig = $context->getResourceConfig();

        $formType = $resourceConfig->getOperationAttribute($context->getOperationName(), 'form', true);
        if (null === $formType) {
             return;
        }

        $options = [
            'validation_groups' => $resourceConfig->getOperationAttribute($context->getOperationName(), 'validation_groups', true),
            'data_class' =>  $this->getFormDataClass($context, $data),
        ];

        return $this->formFactory->create($formType, $data, $options);
    }

    protected function validate(FormInterface $form)
    {
        if (!$form->isRoot()) {
            throw new \InvalidArgumentException('The root form is expected.');
        }
        if (!$form->isSubmitted()) {
            throw new \InvalidArgumentException('The submitted form is expected.');
        }

        return $this->validator->validate($form);
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
