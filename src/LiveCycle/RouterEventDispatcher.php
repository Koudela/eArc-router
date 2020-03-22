<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * router component
 *
 * @package earc/router
 * @link https://github.com/Koudela/earc-router/
 * @copyright Copyright (c) 2018-2020 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\Router;

use eArc\EventTree\Exceptions\InvalidObserverNodeException;
use eArc\EventTree\Exceptions\IsDispatchedException;
use eArc\EventTree\Interfaces\EventDispatcherInterface;
use eArc\EventTree\Interfaces\Transformation\ObserverTreeInterface;
use eArc\EventTree\Interfaces\TreeEventInterface;
use eArc\EventTree\Propagation\PropagationType;
use eArc\EventTree\Transformation\ObserverTree;
use eArc\Router\Interfaces\RouterEventInterface;
use eArc\Router\LiveCycle\RouterLiveCyclePropagationType;

class RouterEventDispatcher implements EventDispatcherInterface
{
    /** @var ObserverTreeInterface */
    protected $observerTree;

    public function __construct()
    {
        $this->observerTree = di_is_decorated(ObserverTreeInterface::class)
            ? di_get(ObserverTreeInterface::class)
            : di_get(ObserverTree::class);
    }

    /**
     * @param TreeEventInterface $event
     *
     * @return TreeEventInterface
     *
     * @throws InvalidObserverNodeException
     * @throws IsDispatchedException
     */
    public function dispatch($event): TreeEventInterface
    {
        foreach ($this->observerTree->getListenersForEvent($event) as $callable) {
            if (!$event instanceof RouterEventInterface) {
                continue;
            }

            $liveCycleEvent = new RouterLiveCycleEvent(
                new RouterLiveCyclePropagationType(['earc-router-livecycle', 'pre-call'], [], null),
                $event,
                $callable
            );

            $liveCycleEvent->dispatch();
        }

        return $event;
    }
}