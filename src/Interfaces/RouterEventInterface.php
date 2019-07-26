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

namespace eArc\router\Interfaces;

use eArc\EventTree\Interfaces\TreeEventInterface;
use eArc\router\Exceptions\NoRequestInformationException;

interface RouterEventInterface extends TreeEventInterface
{
    /**
     * Get the request information of the event.
     *
     * @param string $requestType
     *
     * @return RequestInformationInterface
     *
     * @throws NoRequestInformationException
     */
    public function getRequestInformation(string $requestType = 'GET'): RequestInformationInterface;

    /**
     * Get the route information of the event.
     *
     * @return RouteInformationInterface
     */
    public function getRouteInformation(): RouteInformationInterface;
}