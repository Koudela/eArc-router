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

use eArc\EventTree\TreeEvent;
use eArc\Router\Immutables\Request;
use eArc\Router\Immutables\Route;
use eArc\Router\Interfaces\RouterListenerInterface;
use eArc\Router\Interfaces\RequestInformationInterface;
use eArc\Router\Interfaces\RouteInformationInterface;
use eArc\Router\Interfaces\RouterEventInterface;
use eArc\Router\LiveCycle\RouterLiveCyclePropagationType;
use eArc\Router\Service\RouterService;

class RouterEvent extends TreeEvent implements RouterEventInterface
{
    /** @var RequestInformationInterface */
    protected $request;

    /** @var RouteInformationInterface */
    protected $route;

    public function __construct(?string $uri = null, ?string $requestMethod = null, ?array $argv = null)
    {
        $path = preg_replace(['#.*//[^/]+#', '#\?.*#'], ['', ''], $uri ?? $_SERVER['REQUEST_URI']);
        $routingDir = RouterService::getRoutingDir(RouterEvent::class);

        $this->route = new Route($path, $routingDir);

        $this->request = new Request($requestMethod, $argv);

        parent::__construct(new RouterLiveCyclePropagationType(
            [$routingDir],
            $this->route->getDirs(),
            0
        ));
    }

    public function getRequest(): RequestInformationInterface
    {
        return $this->request;
    }

    public function getRoute(): RouteInformationInterface
    {
        return $this->route;
    }

    public static function getApplicableListener(): array
    {
        return [RouterListenerInterface::class];
    }

    public function __sleep(): array
    {
        return ['propagationType', 'route', 'request'];
    }
}
