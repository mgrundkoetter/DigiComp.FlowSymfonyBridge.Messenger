<?php

namespace DigiComp\FlowSymfonyBridge\Messenger\Tests\Functional\Fixtures\Message;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TestMessageHandler
{
    public function __invoke(TestMessage $message)
    {
        //do nothing for now
    }
}
