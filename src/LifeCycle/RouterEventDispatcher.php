<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * router component
 *
 * @package earc/router
 * @link https://github.com/Koudela/earc-router/
 * @copyright Copyright (c) 2018-2021 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\Router\LifeCycle;

use eArc\EventTree\EventDispatcher;
use eArc\EventTree\Exceptions\InvalidObserverNodeException;
use eArc\EventTree\Exceptions\IsDispatchedException;
use eArc\EventTree\Exceptions\UnsuitableEventException;
use eArc\EventTree\Interfaces\Transformation\ObserverTreeInterface;
use eArc\EventTree\Interfaces\TreeEventInterface;
use eArc\EventTree\Propagation\PropagationType;
use eArc\Router\Interfaces\RouterEventInterface;

class RouterEventDispatcher extends EventDispatcher
{
    /** @var ObserverTreeInterface */
    protected $observerTree;

    /**
     * @param TreeEventInterface $event
     *
     * @return TreeEventInterface
     *
     * @throws InvalidObserverNodeException
     * @throws IsDispatchedException
     * @throws UnsuitableEventException
     */
    public function dispatch($event): TreeEventInterface
    {
        foreach ($this->observerTree->getListenersForEvent($event) as $callable) {
            if (!$event instanceof RouterEventInterface) {
                continue;
            }

            $lifeCycleEvent = new RouterLifeCycleEvent(
                new PropagationType(['earc', 'lifecycle', 'router'], [], null),
                $event,
                $callable
            );

            $lifeCycleEvent->dispatch();
        }

        return $event;
    }
}
