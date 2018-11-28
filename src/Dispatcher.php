<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/router
 * @link https://github.com/Koudela/earc-router/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\Router;

use eArc\EventTree\Event;
use eArc\EventTree\Propagation\EventRouter;
use eArc\EventTree\Type;
use eArc\ObserverTree\Observer;
use eArc\Router\Immutables\Request;
use eArc\Router\Immutables\Route;

/**
 * Initializes Request and Route immutables. Dispatches the routing/request
 * Event on the routing observer tree.
 */
class Dispatcher
{
    /** @var Observer */
    protected $observerTree;

    /** @var Event */
    protected $rootEvent;

    /**
     * @param Event|null $rootEvent
     */
    public function __construct(Event $rootEvent)
    {
        $this->rootEvent = $rootEvent;
    }

    /**
     * Starts the lifecycle of the request/route event.
     *
     * @param string $url
     * @param array|null $requestArgs
     * @param string $requestType
     */
    public function dispatch(string $url, array $requestArgs = null, $requestType = 'GET'): void
    {
        $route = new Route($this->rootEvent->getType()->getTree(), $url);

        $request = new Request($requestArgs, $requestType);

        $event = new Event(
            $this->rootEvent,
            new Type(
                $this->rootEvent->getType()->getTree(),
                [],
                $route->getRealArgs(),
                $route->cntRealArgs()
            ),
            false
        );

        $event->getPayload()->set('route', $route, true);

        $event->getPayload()->set('request', $request, true);

        (new EventRouter($event))->dispatchEvent();
    }
}
