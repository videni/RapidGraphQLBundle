<?php

namespace Videni\Bundle\RestBundle\Form\DataMapper;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * The data mapper that is used in "delete_relationship" Data API action.
 */
class RemoveRelationshipMapper extends AbstractRelationshipMapper
{
    /**
     * {@inheritdoc}
     */
    protected function mapDataToCollectionFormField(
        $data,
        FormInterface $formField,
        PropertyPathInterface $propertyPath
    ) {
        // do nothing here because only input collection items should be processed by the form
    }

    /**
     * {@inheritdoc}
     */
    protected function mapCollectionFormFieldToData(
        $data,
        FormInterface $formField,
        PropertyPathInterface $propertyPath
    ) {
        $methods = $this->findAdderAndRemover($data, (string)$propertyPath);
        if ($methods) {
            $formData = $formField->getData();
            foreach ($formData as $value) {
                $data->{$methods[1]}($this->resolveEntity($value));
            }
        } else {
            /** @var Collection $dataValue */
            $dataValue = $this->propertyAccessor->getValue($data, $propertyPath);
            $formData = $formField->getData();
            foreach ($formData as $value) {
                $dataValue->removeElement($this->resolveEntity($value));
            }
        }
    }
}
