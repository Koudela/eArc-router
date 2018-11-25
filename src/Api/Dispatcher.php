<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/router
 * @link https://github.com/Koudela/earc-router/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\Router\Api;

use eArc\EventTree\Event\Event;
use eArc\EventTree\Tree\EventRouter;
use eArc\EventTree\Tree\ObserverTree;
use eArc\Router\Immutables\Request;
use eArc\Router\Immutables\Route;

/**
 * Initializes Request and Route immutables. Dispatches the routing/request
 * Event on the routing observer tree.
 */
class Dispatcher
{
    /** @var ObserverTree */
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
        $route = new Route($this->rootEvent->getTree(), $url);

        $request = new Request($requestArgs, $requestType);

        $this->animate($route, $request);
    }

    /**
     * Starts the lifecycle of the request/route event.
     *
     * @param Route $route
     * @param Request $request
     */
    public function animate(Route $route, Request $request): void
    {
        $event = new Event(
            $this->rootEvent,
            $this->rootEvent->getTree(),
            [],
            $route->getRealArgs(),
            $route->cntRealArgs(),
            true
        );

        $event->setPayload('route', $route, true);

        $event->setPayload('request', $request, true);

        (new EventRouter($event))->dispatchEvent();
    }
}
