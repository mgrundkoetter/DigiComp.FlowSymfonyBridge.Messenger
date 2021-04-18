<?php

namespace DigiComp\FlowSymfonyBridge\Messenger\Tests\Functional\Fixtures\Message;

use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class FailingTestMessageHandler implements MessageSubscriberInterface
{
    public static function getHandledMessages(): iterable
    {
        yield FailingMessage::class => [];
    }

    public function __invoke(FailingMessage $message)
    {
        throw new \Exception('bang!');
    }
}
