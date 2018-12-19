<?php

namespace Videni\Bundle\RestBundle\Decoder;

use Psr\Container\ContainerInterface as PsrContainerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides encoders through the Symfony DIC.
 * */
class ContainerDecoderProvider implements DecoderProviderInterface
{
    private $container;
    private $decoders;

    /**
     * Constructor.
     *
     * @param PsrContainerInterface $container The container from which the actual decoders are retrieved
     * @param array                 $decoders  List of key (format) value (service ids) of decoders
     */
    public function __construct($container, array $decoders)
    {
        if (!$container instanceof PsrContainerInterface && !$container instanceof ContainerInterface) {
            throw new \InvalidArgumentException(sprintf('The container must be an instance of %s or %s (%s given).', PsrContainerInterface::class, ContainerInterface::class, is_object($container) ? get_class($container) : gettype($container)));
        }

        $this->container = $container;
        $this->decoders = $decoders;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($format)
    {
        return isset($this->decoders[$format]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDecoder($format)
    {
        if (!$this->supports($format)) {
            throw new \InvalidArgumentException(
                sprintf("Format '%s' is not supported by ContainerDecoderProvider.", $format)
            );
        }

        return $this->container->get($this->decoders[$format]);
    }
}
