<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\ExpressionLanguage;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\ExpressionLanguage as BaseExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheAdapter;
use Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheInterface;

final class ExpressionLanguage extends BaseExpressionLanguage
{
    /**
     * {@inheritdoc}
     */
    public function __construct($cache = null, array $providers = [])
    {
        if (null !== $cache) {
            if ($cache instanceof ParserCacheInterface) {
                @trigger_error(sprintf('Passing an instance of %s as constructor argument for %s is deprecated as of Sylius 1.2 and will be removed in 2.0. Pass an instance of %s instead.', ParserCacheInterface::class, self::class, CacheItemPoolInterface::class), E_USER_DEPRECATED);

                $cache = new ParserCacheAdapter($cache);
            } elseif (!$cache instanceof CacheItemPoolInterface) {
                throw new \InvalidArgumentException(sprintf('Cache argument has to implement %s.', CacheItemPoolInterface::class));
            }
        }

        array_unshift($providers, new NotNullExpressionFunctionProvider());

        parent::__construct($cache, $providers);
    }
}
