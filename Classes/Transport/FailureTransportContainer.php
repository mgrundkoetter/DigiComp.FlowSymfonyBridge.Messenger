<?php

namespace DigiComp\FlowSymfonyBridge\Messenger\Transport;

use Neos\Flow\Annotations as Flow;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @Flow\Scope("singleton")
 */
class FailureTransportContainer implements ContainerInterface
{
    /**
     * @var TransportInterface[]
     */
    protected array $transports;

    public function get(string $id)
    {
        return $this->transports[$id];
    }

    public function has(string $id)
    {
        return isset($this->transports[$id]);
    }

    public function set(string $id, TransportInterface $transport)
    {
        $this->transports[$id] = $transport;
    }
}
