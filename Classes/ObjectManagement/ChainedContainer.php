<?php

namespace DigiComp\FlowSymfonyBridge\Messenger\ObjectManagement;

use Psr\Container\ContainerInterface;

class ChainedContainer implements ContainerInterface
{
    private array $childContainers;

    public function __construct(ContainerInterface ...$childContainers)
    {
        $this->childContainers = $childContainers;
    }

    public function get(string $id)
    {
        foreach ($this->childContainers as $childContainer) {
            if ($childContainer->has($id)) {
                return $childContainer->get($id);
            }
        }
        throw new \InvalidArgumentException('Service id is unknown: ' . $id);
    }

    public function has(string $id): bool
    {
        foreach ($this->childContainers as $childContainer) {
            if ($childContainer->has($id)) {
                return true;
            }
        }
        return false;
    }
}
