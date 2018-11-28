<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\ExpressionLanguage;

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
                $cache = new ParserCacheAdapter($cache);
            } elseif (!$cache instanceof CacheItemPoolInterface) {
                throw new \InvalidArgumentException(sprintf('Cache argument has to implement %s.', CacheItemPoolInterface::class));
            }
        }

        array_unshift($providers, new NotNullExpressionFunctionProvider());

        parent::__construct($cache, $providers);
    }
}
