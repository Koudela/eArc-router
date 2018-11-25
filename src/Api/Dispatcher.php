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

use eArc\eventTree\Event\Event;
use eArc\eventTree\Tree\EventRouter;
use eArc\eventTree\Tree\ObserverTree;
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
     * @param ObserverTree $observerTree
     * @param Event $event
     */
    public function __construct(ObserverTree $observerTree, Event $event = null)
    {
        $this->observerTree = $observerTree;
        $this->rootEvent = $event;
    }

    /**
     * Starts the lifecycle of the request event.
     *
     * @param string $url
     * @param array|null $requestArgs
     * @param string $requestType
     */
    public function dispatch(string $url, array $requestArgs = null, $requestType = 'GET'): void
    {
        $route = new Route($this->observerTree, $url);

        $event = new Event(
            $this->rootEvent,
            $this->observerTree,
            [],
            $route->getRealArgs(),
            $route->cntRealArgs(),
            true
        );

        $event->setPayload('route', $route);

        $event->setPayload('request', new Request($requestArgs, $requestType));

        (new EventRouter($event))->dispatchEvent();
    }
}
