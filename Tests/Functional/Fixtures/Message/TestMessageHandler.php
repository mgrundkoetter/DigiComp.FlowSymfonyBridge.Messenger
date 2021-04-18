<?php

namespace DigiComp\FlowSymfonyBridge\Messenger\Tests\Functional\Fixtures\Message;

use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class TestMessageHandler implements MessageSubscriberInterface
{
    public static function getHandledMessages(): iterable
    {
        yield TestMessage::class => [];
    }

    public function __invoke(TestMessage $message)
    {
        //do nothing for now
    }
}
