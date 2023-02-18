<?php

namespace DigiComp\FlowSymfonyBridge\Messenger;

use DigiComp\FlowSymfonyBridge\Messenger\ObjectManagement\RewindableGenerator;
use Neos\Flow\Annotations as Flow;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\MessageBus;

#[Flow\Scope('singleton')]
class MessageBusContainer implements ContainerInterface
{
    #[Flow\InjectConfiguration(path: 'buses')]
    protected array $configuration;

    /**
     * @var MessageBus[]
     */
    protected array $buses = [];

    public function get(string $id)
    {
        if (! isset($this->buses[$id])) {
            $middlewares = new RewindableGenerator($this->configuration[$id]['middleware']);
            $this->buses[$id] = new MessageBus($middlewares);
        }
        return $this->buses[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->configuration[$id]);
    }
}
