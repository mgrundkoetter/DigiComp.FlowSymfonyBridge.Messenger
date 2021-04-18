<?php

namespace DigiComp\FlowSymfonyBridge\Messenger;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

// TODO: Maybe an own package for EntityManager bridge?
class EventDispatcherFactory
{
    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\InjectConfiguration
     * @var array
     */
    protected $configuration;

    public function create()
    {
        $eventDispatcher = new EventDispatcher();

        foreach ($this->configuration['eventDispatcher']['subscribers'] as $subscriberId => $enabled) {
            if ($subscriberId === null || ! (bool) $enabled) {
                continue;
            }
            $this->addLazySubscribers($eventDispatcher, $subscriberId);
        }
        return $eventDispatcher;
    }

    private function addLazySubscribers(EventDispatcherInterface $eventDispatcher, $subscriberId)
    {
        $subscriberClass = $this->objectManager->getClassNameByObjectName($subscriberId);
        if (! is_a($subscriberClass, EventSubscriberInterface::class, true)) {
            throw new \RuntimeException(
                'Object with name ' . $subscriberId . ' is not an EventSubscriberInterface',
                1618753949
            );
        }

        foreach ($subscriberClass::getSubscribedEvents() as $eventName => $params) {
            if (\is_string($params)) {
                $callClosure = function (...$arguments) use ($subscriberId, $params) {
                    $subscriber = $this->objectManager->get($subscriberId);
                    $method = $params;
                    return $subscriber->$method(...$arguments);
                };
                $eventDispatcher->addListener($eventName, $callClosure);
            } elseif (\is_string($params[0])) {
                $callClosure = function (...$arguments) use ($subscriberId, $params) {
                    $subscriber = $this->objectManager->get($subscriberId);
                    $method = $params[0];
                    return $subscriber->$method(...$arguments);
                };
                $eventDispatcher->addListener($eventName, $callClosure, $params[1] ?? 0);
            } else {
                foreach ($params as $listener) {
                    $callClosure = function (...$arguments) use ($subscriberId, $listener) {
                        $subscriber = $this->objectManager->get($subscriberId);
                        $method = $listener[0];
                        return $subscriber->$method(...$arguments);
                    };
                    $eventDispatcher->addListener($eventName, $callClosure, $listener[1] ?? 0);
                }
            }
        }
    }
}
