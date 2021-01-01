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

namespace eArc\Router\LifeCycle;

use eArc\EventTree\Propagation\PropagationType;
use eArc\EventTree\TreeEvent;
use eArc\Router\Interfaces\RouterEventInterface;

class RouterLifeCycleEvent extends TreeEvent
{
    /** @var RouterEventInterface */
    public $routerEvent;

    /** @var callable [RouterListenerInterface, 'process'] */
    public $listenerCallable;

    public function __construct(PropagationType $propagationType, RouterEventInterface $routerEvent, callable $controllerCallable)
    {
        parent::__construct($propagationType);

        $this->routerEvent = $routerEvent;
        $this->listenerCallable = $controllerCallable;
    }
}
