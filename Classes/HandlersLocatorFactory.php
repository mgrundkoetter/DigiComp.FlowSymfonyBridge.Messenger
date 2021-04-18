<?php

namespace DigiComp\FlowSymfonyBridge\Messenger;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Reflection\ReflectionService;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class HandlersLocatorFactory
{
    /**
     * @Flow\InjectConfiguration
     * @var array
     */
    protected $configuration;

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\Inject
     * @var ReflectionService
     */
    protected $reflectionService;

    public function create($busName = 'default')
    {
        $messageHandlerClasses = $this->reflectionService
            ->getAllImplementationClassNamesForInterface(MessageSubscriberInterface::class);
        $handlerDescriptors = [];
        foreach ($messageHandlerClasses as $messageHandlerClass) {
            foreach ($messageHandlerClass::getHandledMessages() as $messageName => $config) {
                if (! is_array($config)) {
                    throw new \InvalidArgumentException(
                        'different from doctrine, we (currently) need subscribers to always have an option array'
                    );
                }
                if (isset($config['bus']) && $config['bus'] !== $busName) {
                    continue;
                }
                $handlerDescriptors[$messageName][] = new HandlerDescriptor(
                    $this->objectManager->get($messageHandlerClass),
                    $config
                );
            }
        }
        // TODO: Maybe we can allow handlers to be added to bus or globally by configuration?

        return new HandlersLocator($handlerDescriptors);
    }
}
