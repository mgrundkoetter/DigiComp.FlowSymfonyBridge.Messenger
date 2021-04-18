<?php

namespace DigiComp\FlowSymfonyBridge\Messenger;

use DigiComp\FlowSymfonyBridge\Messenger\ObjectManagement\RewindableGenerator;
use Neos\Flow\Annotations as Flow;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\MessageBus;

/**
 * @Flow\Scope("singleton")
 */
class MessageBusContainer implements ContainerInterface
{
    /**
     * @Flow\InjectConfiguration(path="buses")
     * @var array
     */
    protected $configuration;

    /**
     * @var MessageBus[]
     */
    protected array $buses = [];

    /**
     * @inheritDoc
     */
    public function get(string $id)
    {
        if (! isset($this->buses[$id])) {
            $middlewares = new RewindableGenerator($this->configuration[$id]['middleware']);
            $this->buses[$id] = new MessageBus($middlewares);
        }
        return $this->buses[$id];
    }

    /**
     * @inheritDoc
     */
    public function has(string $id)
    {
        return isset($this->configuration[$id]);
    }
}
