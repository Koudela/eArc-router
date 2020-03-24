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

namespace eArc\Router\Interfaces;

use eArc\EventTree\Interfaces\TreeEventInterface;

interface RouterEventInterface extends TreeEventInterface
{
    /**
     * Get the request information of the event.
     *
     * @return RequestInformationInterface
     */
    public function getRequest(): RequestInformationInterface;

    /**
     * Get the route information of the event.
     *
     * @return RouteInformationInterface
     */
    public function getRoute(): RouteInformationInterface;
}
