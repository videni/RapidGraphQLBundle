<?php

namespace App\Bundle\RestBundle\Processor\Shared;

use App\Bundle\RestBundle\Processor\Context;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use App\Bundle\RestBundle\Validator\ValidatorInterface;

final class ValidationProcessor implements ProcessorInterface
{
    private $validator;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        ValidatorInterface $validator
    ) {
        $this->validator = $validator;
    }

     /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        $validationGroups = $context->getResourceConfig()->getOperationAttribute($context->getClassName(), 'validation_groups');

        $this->validator->validate($context->getResult(), ['groups' => $validationGroups]);
    }
}
