<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Validator;

use Videni\Bundle\RestBundle\Validator\Exception\ValidationException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidatorInterface;

/**
 * Validates an item using the Symfony validator component.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class Validator implements ValidatorInterface
{
    private $validator;
    private $container;

    public function __construct(SymfonyValidatorInterface $validator, ContainerInterface $container = null)
    {
        $this->validator = $validator;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($data, array $context = [])
    {
        if (null !== $validationGroups = $context['groups'] ?? null) {
            if ($this->container &&
                \is_string($validationGroups) &&
                $this->container->has($validationGroups) &&
                ($service = $this->container->get($validationGroups)) &&
                \is_callable($service)
            ) {
                $validationGroups = $service($data);
            } elseif (\is_callable($validationGroups)) {
                $validationGroups = $validationGroups($data);
            }

            if (!$validationGroups instanceof GroupSequence) {
                $validationGroups = (array) $validationGroups;
            }
        }

        $violations = $this->validator->validate($data, null, $validationGroups);
        if (0 !== \count($violations)) {
            throw new ValidationException($violations);
        }
    }
}
