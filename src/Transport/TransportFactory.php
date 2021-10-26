<?php

declare(strict_types=1);

namespace SchedulerBundle\Transport;

use SchedulerBundle\Exception\InvalidArgumentException;
use SchedulerBundle\SchedulePolicy\SchedulePolicyOrchestratorInterface;
use SchedulerBundle\Transport\Configuration\ConfigurationInterface;
use Symfony\Component\Serializer\SerializerInterface;
use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TransportFactory
{
    /**
     * @var TransportFactoryInterface[]
     */
    private iterable $factories;

    /**
     * @param TransportFactoryInterface[] $transportsFactories
     */
    public function __construct(iterable $transportsFactories)
    {
        $this->factories = $transportsFactories;
    }

    /**
     * @param string                              $dsn
     * @param array<string|int, mixed>            $options
     * @param ConfigurationInterface              $configuration
     * @param SerializerInterface                 $serializer
     * @param SchedulePolicyOrchestratorInterface $schedulePolicyOrchestrator
     *
     * @return TransportInterface
     */
    public function createTransport(
        string $dsn,
        array $options,
        ConfigurationInterface $configuration,
        SerializerInterface $serializer,
        SchedulePolicyOrchestratorInterface $schedulePolicyOrchestrator
    ): TransportInterface {
        foreach ($this->factories as $factory) {
            if ($factory->support($dsn, $options)) {
                return $factory->createTransport(Dsn::fromString($dsn), $options, $configuration, $serializer, $schedulePolicyOrchestrator);
            }
        }

        throw new InvalidArgumentException(sprintf('No transport supports the given Scheduler DSN "%s".', $dsn));
    }
}
