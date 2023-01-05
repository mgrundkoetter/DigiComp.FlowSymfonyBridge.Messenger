<?php

namespace DigiComp\FlowSymfonyBridge\Messenger\Tests\Functional;

use DigiComp\FlowSymfonyBridge\Messenger\Tests\Functional\Fixtures\Message\FailingMessage;
use DigiComp\FlowSymfonyBridge\Messenger\Tests\Functional\Fixtures\Message\TestMessage;
use DigiComp\FlowSymfonyBridge\Messenger\Transport\TransportsContainer;
use Neos\Flow\Tests\FunctionalTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineTransport;
use Symfony\Component\Messenger\EventListener\StopWorkerOnMessageLimitListener;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\InMemoryTransport;
use Symfony\Component\Messenger\Worker;

class BusTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function itSendsAsyncMessage()
    {
        $messageBus = $this->objectManager->get(MessageBusInterface::class);

        $messageBus->dispatch(new TestMessage('Hallo Welt!'));
        $sendersContainer = $this->objectManager->get(TransportsContainer::class);

        /** @var InMemoryTransport $transport1 */
        $transport1 = $sendersContainer->get('test-in-memory-1');
        /** @var InMemoryTransport $transport2 */
        $transport2 = $sendersContainer->get('test-in-memory-2');
        /** @var DoctrineTransport $transport3 */
        $transport3 = $sendersContainer->get('test-doctrine');
        $this->assertInstanceOf(InMemoryTransport::class, $transport1);
        $this->assertInstanceOf(InMemoryTransport::class, $transport2);
        $this->assertInstanceOf(DoctrineTransport::class, $transport3);
        $this->assertCount(1, $transport1->getSent());
        $this->assertCount(1, $transport2->getSent());
        $this->assertCount(1, $transport3->all());
        $this->assertCount(0, $transport1->getAcknowledged());
        $this->assertCount(0, $transport2->getAcknowledged());
        $this->assertCount(1, $transport3->all());

        $eventDispatcher = $this->objectManager->get('DigiComp.FlowSymfonyBridge.Messenger:EventDispatcher');
        $eventDispatcher->addSubscriber(new StopWorkerOnMessageLimitListener(1));
        foreach (['test-in-memory-1', 'test-in-memory-2', 'test-doctrine'] as $transportId) {
            $worker = new Worker([$transportId => $sendersContainer->get($transportId)], $messageBus, $eventDispatcher);
            $worker->run();
        }
        // TODO: Check for success on all workers - doctrine does not seem to get executed
        $this->assertCount(1, $transport1->getAcknowledged());
        $this->assertCount(1, $transport2->getAcknowledged());
        $this->assertCount(0, $transport3->all());
    }

    /**
     * @test
     */
    public function itRetriesFailingMessages()
    {
        $messageBus = $this->objectManager->get(MessageBusInterface::class);

        $messageBus->dispatch(new FailingMessage());
        $sendersContainer = $this->objectManager->get(TransportsContainer::class);

        /** @var DoctrineTransport $transport1 */
        $transport1 = $sendersContainer->get('test-retry-doctrine');
        /** @var DoctrineTransport $failedTransport */
        $failedTransport = $sendersContainer->get('test-failed-doctrine');
        $this->assertCount(1, $transport1->all());
        $this->assertCount(0, $failedTransport->all());

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->objectManager->get('DigiComp.FlowSymfonyBridge.Messenger:EventDispatcher');
        $eventDispatcher->addSubscriber(new StopWorkerOnMessageLimitListener(1));
        $worker = new Worker(
            ['test-retry-doctrine' => $sendersContainer->get('test-retry-doctrine')],
            $messageBus,
            $eventDispatcher
        );
        $worker->run();
        $this->assertCount(1, $transport1->all());
        $this->assertCount(0, $failedTransport->all());

        $worker = new Worker(
            ['test-retry-doctrine' => $sendersContainer->get('test-retry-doctrine')],
            $messageBus,
            $eventDispatcher
        );
        $worker->run();
        $this->assertCount(0, $transport1->all());
        $this->assertCount(1, $failedTransport->all());
    }
}
