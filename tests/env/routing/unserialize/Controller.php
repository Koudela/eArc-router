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

namespace eArc\RouterTests\env\routing\unserialize;

use eArc\EventTree\Exceptions\IsNotDispatchedException;
use eArc\Router\Interfaces\RouterEventInterface;
use eArc\RouterTests\env\BaseController;

class Controller extends BaseController
{
    /**
     * @param RouterEventInterface $event
     * @throws IsNotDispatchedException
     */
    public function process(RouterEventInterface $event): void
    {
        parent::process($event);

        $event->getHandler()->kill();

        $event = unserialize($_SESSION['SERIALIZED_EVENT']);
        $event->dispatch();
    }
}
