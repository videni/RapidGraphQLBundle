<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Processor;

use App\Bundle\RestBundle\ExpressionLanguage;
use App\Bundle\RestBundle\ResourceAccessChecker;
use App\Bundle\RestBundle\ResourceAccessCheckerInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use App\Bundle\RestBundle\Processor\Context;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use App\Bundle\RestBundle\Metadata\Resource\ResourceMetadata;
use App\Bundle\RestBundle\Validator\ValidatorInterface;

/**
 * Denies access to the current resource if the logged user doesn't have sufficient permissions.
 */
final class PermissionProcessor
{
    private $resourceAccessChecker;

    public function __construct(
        ResourceAccessCheckerInterface $resourceAccessCheckerOrExpressionLanguage
    ) {
        $this->resourceAccessChecker = $resourceAccessCheckerOrExpressionLanguage;
    }

    /**
     * Sets the applicable format to the HttpFoundation Request.
     *
     *
     * @throws AccessDeniedException
     */
    public function process(Context $context)
    {
        $isGranted = $context->getMetadata()->getOperationAttribute($attributes, 'access_control', null, true);
        if (null === $isGranted) {
            return;
        }

        $extraVariables = $request->attributes->all();
        $extraVariables['object'] = $request->attributes->get('data');
        $extraVariables['request'] = $request;

        if (!$this->resourceAccessChecker->isGranted($attributes['resource_class'], $isGranted, $extraVariables)) {
            throw new AccessDeniedException($resourceMetadata->getOperationAttribute($attributes, 'access_control_message', 'Access Denied.', true));
        }
    }
}
