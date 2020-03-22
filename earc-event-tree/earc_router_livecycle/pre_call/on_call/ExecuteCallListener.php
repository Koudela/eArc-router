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

namespace eArc\RouterEventTreeRoot\earc_router_lifecycle\pre_call\on_call;

use eArc\Observer\Interfaces\EventInterface;
use eArc\Observer\Interfaces\ListenerInterface;
use eArc\Router\RouterLiveCycleEvent;

class ExecuteCallListener implements ListenerInterface
{
    /**
     * @inheritDoc
     */
    public function process(EventInterface $event): void
    {
        if ($event instanceof RouterLiveCycleEvent) {
            call_user_func($event->controllerCallable, $event->routerEvent);
        }
    }
}