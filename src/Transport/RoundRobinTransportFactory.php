<?php

declare(strict_types=1);

namespace SchedulerBundle\Transport;

use SchedulerBundle\SchedulePolicy\SchedulePolicyOrchestratorInterface;
use SchedulerBundle\Transport\Configuration\ConfigurationInterface;
use Symfony\Component\Serializer\SerializerInterface;
use function strpos;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class RoundRobinTransportFactory extends AbstractCompoundTransportFactory
{
    /**
     * @var TransportFactoryInterface[]
     */
    private iterable $transportFactories;

    /**
     * @param TransportFactoryInterface[] $transportFactories
     */
    public function __construct(iterable $transportFactories)
    {
        $this->transportFactories = $transportFactories;
    }

    /**
     * {@inheritdoc}
     */
    public function createTransport(
        Dsn $dsn,
        array $options,
        ConfigurationInterface $configuration,
        SerializerInterface $serializer,
        SchedulePolicyOrchestratorInterface $schedulePolicyOrchestrator
    ): RoundRobinTransport {
        $configuration->init([
            'quantum' => $dsn->getOptionAsInt('quantum', 2),
        ], [
            'quantum' => 'int',
        ]);

        return new RoundRobinTransport(
            $this->handleTransportDsn(' && ', $dsn, $this->transportFactories, $options, $configuration, $serializer, $schedulePolicyOrchestrator),
            $configuration
        );
    }

    /**
     * {@inheritdoc}
     */
    public function support(string $dsn, array $options = []): bool
    {
        return 0 === strpos($dsn, 'roundrobin://') || 0 === strpos($dsn, 'rr://');
    }
}
