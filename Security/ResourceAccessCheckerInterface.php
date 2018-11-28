<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Security;

/**
 * Checks if the logged user has sufficient permissions to access the given resource.
 */
interface ResourceAccessCheckerInterface
{
    /**
     * Checks if the given item can be accessed by the current user.
     */
    public function isGranted(string $resourceClass, string $expression, array $extraVariables = []): bool;
}
