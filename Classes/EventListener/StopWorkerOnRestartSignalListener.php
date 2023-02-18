<?php

namespace DigiComp\FlowSymfonyBridge\Messenger\EventListener;

use Neos\Flow\Annotations as Flow;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\Event\WorkerStartedEvent;

// This is a 1 to one copy of the original event listener, with a modified RESTART_REQUESTED_TIMESTAMP_KEY to match
// the restriction of the cache ids in flow.
// Additionally, the DI is simplified

#[Flow\Scope('singleton')]
class StopWorkerOnRestartSignalListener implements EventSubscriberInterface
{
    public const RESTART_REQUESTED_TIMESTAMP_KEY = 'workers_restart_requested_timestamp';

    #[Flow\Inject(name: 'DigiComp.FlowSymfonyBridge.Messenger:RestartSignalCachePool', lazy: false)]
    protected CacheItemPoolInterface $cachePool;

    #[Flow\Inject(lazy: false)]
    protected LoggerInterface $logger;
    private float $workerStartedAt;

    public function onWorkerStarted(): void
    {
        $this->workerStartedAt = \microtime(true);
    }

    public function onWorkerRunning(WorkerRunningEvent $event): void
    {
        if ($this->shouldRestart()) {
            $event->getWorker()->stop();
            $this->logger->info('Worker stopped because a restart was requested.');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerStartedEvent::class => 'onWorkerStarted',
            WorkerRunningEvent::class => 'onWorkerRunning',
        ];
    }

    private function shouldRestart(): bool
    {
        $cacheItem = $this->cachePool->getItem(self::RESTART_REQUESTED_TIMESTAMP_KEY);

        if (!$cacheItem->isHit()) {
            // no restart has ever been scheduled
            return false;
        }

        return $this->workerStartedAt < $cacheItem->get();
    }
}
