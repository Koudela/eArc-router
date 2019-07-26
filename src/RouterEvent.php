<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * router component
 *
 * @package earc/router
 * @link https://github.com/Koudela/earc-router/
 * @copyright Copyright (c) 2018-2019 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\router;

use eArc\EventTree\Propagation\PropagationType;
use eArc\EventTree\TreeEvent;
use eArc\router\Exceptions\NoRequestInformationException;
use eArc\Router\Immutables\Request;
use eArc\Router\Immutables\Route;
use eArc\router\Interfaces\ControllerInterface;
use eArc\Router\Interfaces\RequestInformationInterface;
use eArc\Router\Interfaces\RouteInformationInterface;
use eArc\router\Interfaces\RouterEventInterface;

class RouterEvent extends TreeEvent implements RouterEventInterface
{
    /** @var RequestInformationInterface[] */
    protected $requestInformation = [];

    /** @var RouteInformationInterface */
    protected $routeInformation;

    public function __construct(string $url, $requestTypes = ['GET', 'POST'], array $requestArgs = null)
    {
        $this->routeInformation = new Route($url);

        foreach ($requestTypes as $requestType) {
            $args = isset($requestArgs[$requestType]) ? $requestArgs[$requestType] : null;
            $request[$requestType] = new Request($args, $requestType);
        }

        $propagationType = new PropagationType(
            [di_param('earc.router.directory')],
            $this->routeInformation->getRealArgs(),
            0
        );

        parent::__construct($propagationType);
    }

    public function getRequestInformation(string $requestType = 'GET'): RequestInformationInterface
    {
        if (!isset($this->requestInformation[$requestType])) {
            throw new NoRequestInformationException(sprintf('No request information is available for %s.', $requestType));
        }

        return $this->requestInformation[$requestType];
    }

    public function getRouteInformation(): RouteInformationInterface
    {
        return $this->routeInformation;
    }

    public static function getApplicableListener(): array
    {
        return [ControllerInterface::class];
    }

    public function __sleep(): array
    {
        return ['propagationType', 'routeInformation', 'requestInformation'];
    }
}
