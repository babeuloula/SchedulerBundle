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
final class FailOverTransportFactory extends AbstractCompoundTransportFactory
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
    ): FailOverTransport {
        $configuration->init([
            'mode' => $options['mode'] ?? $dsn->getOption('mode', 'normal'),
        ], [
            'mode' => 'string',
        ]);

        return new FailOverTransport(
            $this->handleTransportDsn(' || ', $dsn, $this->transportFactories, $options, $configuration, $serializer, $schedulePolicyOrchestrator),
            $configuration
        );
    }

    /**
     * {@inheritdoc}
     */
    public function support(string $dsn, array $options = []): bool
    {
        return 0 === strpos($dsn, 'failover://') || 0 === strpos($dsn, 'fo://');
    }
}
