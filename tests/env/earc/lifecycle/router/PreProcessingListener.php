<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * event tree component
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/eArc-eventTree/
 * @copyright Copyright (c) 2018-2020 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\RouterTests\env\earc\lifecycle\router;

use eArc\EventTree\Interfaces\SortableListenerInterface;
use eArc\Observer\Interfaces\EventInterface;
use eArc\Observer\Interfaces\ListenerInterface;
use eArc\Router\Interfaces\RouterEventInterface;
use eArc\Router\LifeCycle\RouterLifeCycleEvent;
use eArc\RouterTests\env\Collector;
use eArc\RouterTests\env\TestLifecycleEvent;

class PreProcessingListener implements ListenerInterface, SortableListenerInterface
{
    public function process(EventInterface $event) : void
    {
        if ($event instanceof RouterLifeCycleEvent) {
            if ($event->routerEvent instanceof TestLifecycleEvent) {
                /** @var Collector $collector */
                $collector = di_get(Collector::class);
                $collector->calledListener[] = static::class;

                $this->preProcessing($event->routerEvent);
            }
        }
    }

    protected function preProcessing(RouterEventInterface $event)
    {
        /** @var Collector $collector */
        $collector = di_get(Collector::class);
        $collector->calledMethods[] = 'preProcessing('.get_class($event).')';
    }

    public static function getPatience() : float
    {
        return -1;
    }
}
