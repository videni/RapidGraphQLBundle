<?php


declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Validator;

use Videni\Bundle\RestBundle\Validator\Exception\ValidationException;

/**
 * Validates an item using the Symfony validator component.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
interface ValidatorInterface
{
    /**
     * Validates an item.
     *
     * @param object $data
     *
     * @throws ValidationException
     */
    public function validate($data, array $context = []);
}
