<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Security;

use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Checks if the logged user has sufficient permissions to access the given resource.
 * */
final class ResourceAccessChecker implements ResourceAccessCheckerInterface
{
    private $expressionLanguage;
    private $container;
    private $authenticationTrustResolver;
    private $roleHierarchy;
    private $tokenStorage;
    private $authorizationChecker;

    public function __construct(
        ExpressionLanguage $expressionLanguage,
        ContainerInterface $container,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        AuthenticationTrustResolverInterface $authenticationTrustResolver,
        RoleHierarchyInterface $roleHierarchy = null
    ) {
        $this->expressionLanguage = $expressionLanguage;
        $this->container = $container;
        $this->authenticationTrustResolver = $authenticationTrustResolver;
        $this->roleHierarchy = $roleHierarchy;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function isGranted(string $expression, array $extraVariables = []): bool
    {
        $variables = array_merge($extraVariables, $this->getVariables($this->tokenStorage->getToken()));

        return (bool) $this->expressionLanguage->evaluate($expression, array_merge($extraVariables, $this->getVariables($this->tokenStorage->getToken())));
    }

    /**
     * @copyright Fabien Potencier <fabien@symfony.com>
     *
     * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Security/Core/Authorization/Voter/ExpressionVoter.php
     */
    private function getVariables(TokenInterface $token): array
    {
        $roles = $this->roleHierarchy ? $this->roleHierarchy->getReachableRoles($token->getRoles()) : $token->getRoles();

        return [
            'token' => $token,
            'user' => $token->getUser(),
            'container' => $this->container,
            'roles' => array_map(function (Role $role) {
                return $role->getRole();
            }, $roles),
            'trust_resolver' => $this->authenticationTrustResolver,
            // needed for the is_granted expression function
            'auth_checker' => $this->authorizationChecker,
        ];
    }
}
