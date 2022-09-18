<?php

namespace DigiComp\FlowSymfonyBridge\Messenger\ObjectManagement;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Utility\PositionalArraySorter;
use Psr\Log\LoggerInterface;

/**
 * Helper for dependency injection. It allows to defer object construction until the list is actually iterated.
 *
 * It filters out service ids which have been set to NULL to make deleting services possible, without overwriting the
 * complete array, allowing to use string keys.
 */
class RewindableGenerator implements \IteratorAggregate, \Countable
{
    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    private array $serviceIds;

    private \Closure $generator;

    public function __construct(array $serviceIds)
    {
        $this->serviceIds = $serviceIds;
        $sortedServiceIds = \array_keys(
            (new PositionalArraySorter($serviceIds))->toArray()
        );
        $this->generator = function () use ($sortedServiceIds) {
            foreach ($sortedServiceIds as $serviceId) {
                if ($serviceId === null) {
                    continue;
                }
                $object = $this->objectManager->get($serviceId);
                // TODO: Thats a quite poor solution to dynamically inject the logger - but it is easy
                if (\method_exists($object, 'setLogger')) {
                    $object->setLogger($this->objectManager->get(LoggerInterface::class));
                }
                yield $object;
            }
        };
    }

    public function getIterator()
    {
        $g = $this->generator;

        return $g();
    }

    public function count()
    {
        return \count($this->serviceIds);
    }
}
