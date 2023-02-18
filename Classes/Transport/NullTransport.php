<?php

namespace DigiComp\FlowSymfonyBridge\Messenger\Transport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;

class NullTransport implements TransportInterface
{
    public function get(): iterable
    {
        return new \EmptyIterator();
    }

    public function ack(Envelope $envelope): void
    {
        // do nothing
    }

    public function reject(Envelope $envelope): void
    {
        // do nothing
    }

    public function send(Envelope $envelope): Envelope
    {
        return $envelope;
    }
}
