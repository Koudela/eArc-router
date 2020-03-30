<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * event tree component
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/eArc-eventTree/
 * @copyright Copyright (c) 2018-2020 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\RouterTests\env\routing\login;

use eArc\EventTree\Exceptions\IsDispatchedException;
use eArc\EventTree\Exceptions\IsNotDispatchedException;
use eArc\Router\Interfaces\RouterEventInterface;
use eArc\Router\RouterEvent;
use eArc\RouterTests\env\BaseController;

class Controller extends BaseController
{
    /**
     * @param RouterEventInterface $event
     *
     * @throws IsNotDispatchedException
     * @throws IsDispatchedException
     */
    public function process(RouterEventInterface $event): void
    {
        parent::process($event);

        $event->getHandler()->kill();
        (new RouterEvent('/error-pages/access-denied', 'GET'))->dispatch();
    }
}
