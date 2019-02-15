<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractResourceType extends AbstractType
{
    /**
     * @var string
     */
    protected $dataClass;

    /**
     * @var string[]
     */
    protected $validationGroups = [];

    /**
     * @param string $dataClass FQCN
     * @param string[] $validationGroups
     */
    public function __construct(string $dataClass, array $validationGroups = [])
    {
        $this->dataClass = $dataClass;
        $this->validationGroups = $validationGroups;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->dataClass,
            'validation_groups' => $this->validationGroups,
        ]);
    }
}
