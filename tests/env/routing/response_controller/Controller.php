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

namespace eArc\RouterTests\env\routing\response_controller;

use eArc\Router\AbstractResponseController;
use eArc\Router\Immutables\Request;
use eArc\Router\Immutables\Route;
use eArc\Router\Interfaces\RequestInformationInterface;
use eArc\Router\Interfaces\ResponseInterface;
use eArc\Router\Interfaces\RouteInformationInterface;
use eArc\Router\Interfaces\RouterEventInterface;
use eArc\Router\RouterEvent;

class Controller extends AbstractResponseController
{
    public function respond(
        string $test,
        RouterEventInterface $eventInterface,
        RouterEvent $event,
        int $argument2,
        string $param1,
        string $param2,
        ?string $nullParameter,
        RouteInformationInterface $routeInterface,
        Route $route,
        RequestInformationInterface $requestInterface,
        Request $request,
        int $argument3 = 23,
    ): ResponseInterface
    {
        return new Response([
            'eventInterface' => $eventInterface,
            'routeInterface' => $routeInterface,
            'requestInterface' => $requestInterface,
            'event' => $event,
            'route' => $route,
            'request' => $request,
            'argument1' => $test,
            'argument2' => $argument2,
            'argument3' => $argument3,
            'param1' => $param1,
            'param2' => $param2,
            'param3' => $nullParameter,
        ]);
    }

    protected function getInputKeyMapping(RouterEventInterface $event): array
    {
        return [
            'param1' => 0,
            'param2' => 1,
            'nullParameter' => 2,
            'test' => 'argument1',
        ];
    }

    protected function getPredefinedTypeHints(RouterEventInterface $event): array
    {
        return array_merge(parent::getPredefinedTypeHints($event), [
            RouterEvent::class => $event,
            Request::class => $event->getRequest(),
            Route::class => $event->getRoute(),
        ]);
    }
}
