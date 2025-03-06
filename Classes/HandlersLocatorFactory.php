<?php

namespace DigiComp\FlowSymfonyBridge\Messenger;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Reflection\ReflectionService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class HandlersLocatorFactory
{
    #[Flow\InjectConfiguration]
    protected array $configuration;

    #[Flow\Inject(lazy: false)]
    protected ObjectManagerInterface $objectManager;

    #[Flow\Inject(lazy: false)]
    protected ReflectionService $reflectionService;

    public function create($busName = 'default'): HandlersLocator
    {
        $handlerDescriptors = [];
        $asHandlerClasses = $this->reflectionService
            ->getClassNamesByAnnotation(AsMessageHandler::class);
        foreach ($asHandlerClasses as $asHandlerClass) {
            /** @var AsMessageHandler[] $annotations */
            $annotations = $this->reflectionService->getClassAnnotations($asHandlerClass, AsMessageHandler::class);
            foreach ($annotations as $annotation) {
                $config['from_transport'] = $annotation->fromTransport;
                $config['priority'] = $annotation->priority;
                $method = $annotation->method ?? '__invoke';
                $messageName = $annotation->handles;
                if ($messageName === null) {
                    $arguments = $this->reflectionService->getMethodParameters($asHandlerClass, $method);
                    $messageName = $arguments[\array_key_first($arguments)]['class'];
                }
                if ($annotation->bus !== null && $annotation->bus !== $busName) {
                    continue;
                }
                $handler = $this->objectManager->get($asHandlerClass);
                $handlerDescriptors[$messageName][] = new HandlerDescriptor(
                    $this->objectManager->get($asHandlerClass),
                    $config
                );
            }
        }
        // TODO: Maybe we can allow handlers to be added to bus or globally by configuration?

        return new HandlersLocator($handlerDescriptors);
    }
}
