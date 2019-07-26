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

interface ControllerInterface
{
    /**
     * Processes the router event.
     *
     * @param RouterEventInterface $event
     */
    public function process(RouterEventInterface $event): void;
}