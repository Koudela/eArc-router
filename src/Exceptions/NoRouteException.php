<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * router component
 *
 * @package earc/router
 * @link https://github.com/Koudela/earc-router/
 * @copyright Copyright (c) 2018-2021 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\Router\Exceptions;

/**
 * The fully qualified class name cannot be transformed to a route since the
 * class does not live in the routing part of the event tree.
 */
class NoRouteException extends BaseException
{
}
