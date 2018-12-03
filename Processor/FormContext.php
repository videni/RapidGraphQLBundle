<?php

namespace Videni\Bundle\RestBundle\Processor;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

/**
 * An interface for form related execution contexts,
 * like contexts for such actions as "create", "update" and modify relationships.
 */
interface FormContext extends ContextInterface
{
    /**
     * Returns request data.
     *
     * @return array
     */
    public function getRequestData();

    /**
     * Sets request data.
     *
     * @param array $requestData
     */
    public function setRequestData(array $requestData);

    /**
     * Checks whether the form builder exists.
     *
     * @return bool
     */
    public function hasFormBuilder();

    /**
     * Gets the form builder.
     *
     * @return FormBuilderInterface|null
     */
    public function getFormBuilder();

    /**
     * Sets the form builder.
     *
     * @param FormBuilderInterface|null $formBuilder
     */
    public function setFormBuilder(FormBuilderInterface $formBuilder = null);

    /**
     * Checks whether the form exists.
     *
     * @return bool
     */
    public function hasForm();

    /**
     * Gets the form.
     *
     * @return FormInterface|null
     */
    public function getForm();

    /**
     * Sets the form.
     *
     * @param FormInterface|null $form
     */
    public function setForm(FormInterface $form = null);

    /**
     * Indicates whether the validation of the form should be skipped or not.
     *
     * @return bool
     */
    public function isFormValidationSkipped();

    /**
     * Sets a flag indicates whether the validation of the form should be skipped or not.
     *
     * @param bool $skipFormValidation
     */
    public function skipFormValidation($skipFormValidation);
}
