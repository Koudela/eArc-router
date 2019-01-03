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
     * @param string[] $requestTypes
     */
    public function dispatch(string $url, array $requestArgs = null, $requestTypes = ['GET', 'POST']): void
    {
        $route = new Route($this->rootEvent->expose(Type::class)->getTree(), $url);

        $request = [];

        if (!empty($requestArgs)) {
            $request['SCRIPT'] = new Request($requestArgs, 'SCRIPT');
        }

        foreach ($requestTypes as $requestType) {
            $request[\strtoupper($requestType)] = new Request($requestArgs, $requestTypes);
        }

        $this->rootEvent->getEventFactory()
            ->inheritPayload(false)
            ->start([])
            ->destination($route->getRealArgs())
            ->maxDepth($route->cntRealArgs() +1)
            ->addPayload(Route::class, $route)
            ->addPayload(Request::class, $request)
            ->build()
            ->dispatch();
    }
}
