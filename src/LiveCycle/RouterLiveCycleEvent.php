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
use eArc\Router\Interfaces\ControllerInterface;
use eArc\Router\Interfaces\RouterEventInterface;
use eArc\Router\LiveCycle\RouterLiveCyclePropagationType;

class RouterLiveCycleEvent extends TreeEvent
{
    /** @var RouterEventInterface */
    public $routerEvent;

    /** @var callable [ControllerInterface, 'process'] */
    public $controllerCallable;

    public function __construct(RouterLiveCyclePropagationType $propagationType, RouterEventInterface $routerEvent, ControllerInterface $controllerCallable)
    {
        parent::__construct($propagationType);

        $this->routerEvent = $routerEvent;
        $this->controllerCallable = $controllerCallable;
    }
}