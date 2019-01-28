<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Security;

use Symfony\Component\Security\Core\Authorization\ExpressionLanguage as BaseExpressionLanguage;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\ExpressionLanguageProvider;

/**
 * Adds some function to the default Symfony Security ExpressionLanguage.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @copyright Fabien Potencier <fabien@symfony.com>
 *
 * @see https://github.com/sensiolabs/SensioFrameworkExtraBundle/blob/master/Security/ExpressionLanguage.php
 */
class ExpressionLanguage extends BaseExpressionLanguage
{
     /**
     * {@inheritdoc}
     */
    public function __construct(CacheItemPoolInterface $cache = null, array $providers = array())
    {
        // prepend the default provider to let users override it easily
        array_unshift($providers, new ExpressionLanguageProvider());

        parent::__construct($cache, $providers);
    }

    protected function registerFunctions()
    {
        parent::registerFunctions();

        $this->register('is_granted', function ($attributes, $object = 'null') {
            return sprintf('$auth_checker->isGranted(%s, %s)', $attributes, $object);
        }, function (array $variables, $attributes, $object = null) {
            return $variables['auth_checker']->isGranted($attributes, $object);
        });
    }
}
