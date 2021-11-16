<?php

declare(strict_types=1);

namespace SchedulerBundle\Trigger;

use Closure;
use SchedulerBundle\Exception\InvalidArgumentException;
use SchedulerBundle\Exception\RuntimeException;
use function array_filter;
use function count;
use function current;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TriggerConfigurationRegistry implements TriggerConfigurationRegistryInterface
{
    /**
     * @var TriggerConfigurationInterface[]
     */
    private iterable $configurationList;

    /**
     * @param TriggerConfigurationInterface[] $configurationList
     */
    public function __construct(iterable $configurationList)
    {
        $this->configurationList = $configurationList;
    }

    public function filter(Closure $func): TriggerConfigurationRegistryInterface
    {
        return new self(array_filter($this->configurationList, $func));
    }

    public function get(string $string): TriggerConfigurationInterface
    {
        $list = $this->filter(static fn (TriggerConfigurationInterface $configuration): bool => $configuration->support($string));
        if (0 === $list->count()) {
            throw new InvalidArgumentException('No configuration found for this trigger');
        }

        if (1 < $list->count()) {
            throw new InvalidArgumentException('More than one configuration found, consider improving the trigger discriminator');
        }

        return $list->current();
    }

    /**
     * {@inheritdoc}
     */
    public function current(): TriggerConfigurationInterface
    {
        $currentConfiguration = current($this->configurationList);
        if (false === $currentConfiguration) {
            throw new RuntimeException('The current configuration cannot be found');
        }

        return $currentConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->configurationList);
    }
}
