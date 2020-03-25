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

use eArc\Observer\Interfaces\EventInterface;
use eArc\Observer\Interfaces\ListenerInterface;

interface RouterListenerInterface extends ListenerInterface
{
    /**
     * Processes the router event.
     *
     * @param RouterEventInterface|EventInterface $event
     */
    public function process(EventInterface $event): void;
}
