<?php

namespace DigiComp\FlowSymfonyBridge\Messenger;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Retry\MultiplierRetryStrategy;
use Symfony\Component\Messenger\Retry\RetryStrategyInterface;

/**
 * @Flow\Scope("singleton")
 */
class RetryStrategiesContainer implements ContainerInterface
{
    /**
     * @Flow\InjectConfiguration
     * @var array
     */
    protected array $configuration;

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var RetryStrategyInterface[]
     */
    protected array $retryStrategies;

    public function get(string $id)
    {
        if (! isset($this->configuration['transports'][$id])) {
            throw new \InvalidArgumentException('Unknown transport name: ' . $id);
        }
        if (! isset($this->retryStrategies[$id])) {
            $strategyDefinition = \array_merge(
                $this->configuration['defaultRetryStrategyOptions'],
                $this->configuration['transports'][$id]['retryStrategy'] ?? []
            );
            if ($strategyDefinition['service']) {
                $this->retryStrategies[$id] = $this->objectManager->get($strategyDefinition['service']);
            } else {
                $this->retryStrategies[$id] = new MultiplierRetryStrategy(
                    $strategyDefinition['maxRetries'],
                    $strategyDefinition['delay'],
                    $strategyDefinition['multiplier'],
                    $strategyDefinition['maxDelay']
                );
            }
        }
        return $this->retryStrategies[$id];
    }

    public function has(string $id)
    {
        return isset($this->configuration['transports'][$id]);
    }
}
