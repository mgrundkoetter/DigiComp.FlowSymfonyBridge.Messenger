<?php

namespace DigiComp\FlowSymfonyBridge\Messenger\Tests\Functional\Fixtures\Message;

class TestMessage
{
    protected string $message;

    /**
     * @param string $message
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
