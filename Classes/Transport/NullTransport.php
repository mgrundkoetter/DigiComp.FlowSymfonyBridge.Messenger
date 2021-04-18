<?php

namespace DigiComp\FlowSymfonyBridge\Messenger\Transport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;

class NullTransport implements TransportInterface
{
    /**
     * @inheritDoc
     */
    public function get(): iterable
    {
        return new \EmptyIterator();
    }

    /**
     * @inheritDoc
     */
    public function ack(Envelope $envelope): void
    {
        // do nothing
    }

    /**
     * @inheritDoc
     */
    public function reject(Envelope $envelope): void
    {
        // do nothing
    }

    /**
     * @inheritDoc
     */
    public function send(Envelope $envelope): Envelope
    {
        return $envelope;
    }
}
