<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * event tree component
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/eArc-eventTree/
 * @copyright Copyright (c) 2018-2021 Thomas Koudela
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

class PostProcessingListener implements ListenerInterface, SortableListenerInterface
{
    public function process(EventInterface $event) : void
    {
        if ($event instanceof RouterLifeCycleEvent) {
            if ($event->routerEvent instanceof TestLifecycleEvent) {
                $collector = di_get(Collector::class);
                $collector->calledListener[] = static::class;

                if ($event->routerEvent instanceof TestLifecycleEvent) {
                    $this->postProcessing($event->routerEvent);
                }
            }
        }
    }

    protected function postProcessing(RouterEventInterface $event)
    {
        $collector = di_get(Collector::class);
        $collector->calledMethods[] = 'postProcessing('.get_class($event).')';
    }

    public static function getPatience() : float
    {
        return 1;
    }
}
