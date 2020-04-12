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

use eArc\Observer\Interfaces\EventInterface;
use eArc\Observer\Interfaces\ListenerInterface;
use eArc\Router\LifeCycle\RouterLifeCycleEvent;
use eArc\RouterTests\env\Collector;
use eArc\RouterTests\env\ParametrizedMethodCallingController;
use eArc\RouterTests\env\TestLifecycleEvent;
use Exception;

class ExecuteCallListener implements ListenerInterface
{
    public function process(EventInterface $event) : void
    {
        if ($event instanceof RouterLifeCycleEvent) {
            if ($event->routerEvent instanceof TestLifecycleEvent) {
                $collector = di_get(Collector::class);
                $collector->calledListener[] = static::class;

                try {
                    $listener = $event->listenerCallable[0];
                    if (!$listener instanceof ParametrizedMethodCallingController) {
                        call_user_func($event->listenerCallable, $event->routerEvent);

                        return;
                    }
                    $actionId = $event->routerEvent->getRoute()->getParam(0);
                    $methodName = $actionId . 'Action';
                    $listener->$methodName($event->routerEvent);
                } catch (Exception $exception) {
                    $this->logException($exception);
                }
            }
        }
    }

    protected function logException(Exception $exception)
    {
        $collector = di_get(Collector::class);
        $collector->calledMethods[] = 'logException('.$exception->getMessage().')';
    }
}
