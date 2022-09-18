<?php

namespace DigiComp\FlowSymfonyBridge\Messenger\Transport;

use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class NullTransportFactory implements TransportFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        return new NullTransport();
    }

    /**
     * @inheritDoc
     */
    public function supports(string $dsn, array $options): bool
    {
        return 0 === \strpos($dsn, 'null://');
    }
}
