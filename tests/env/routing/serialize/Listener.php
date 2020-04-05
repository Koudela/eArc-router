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

namespace eArc\RouterTests\env\routing\serialize;

use eArc\EventTree\Exceptions\IsDispatchedException;
use eArc\EventTree\Exceptions\IsNotDispatchedException;
use eArc\Router\Interfaces\RouterEventInterface;
use eArc\Router\RouterEvent;
use eArc\RouterTests\env\BaseListener;

class Listener extends BaseListener
{
    protected static $hits = 0;

    /**
     * @param RouterEventInterface $event
     *
     * @throws IsNotDispatchedException
     * @throws IsDispatchedException
     */
    public function process(RouterEventInterface $event): void
    {
        parent::process($event);

        if (self::$hits++ < 1) {
            $_SESSION['SERIALIZED_EVENT'] = serialize($event);

            $event->getHandler()->kill();

            (new RouterEvent('/unserialize', 'GET'))->dispatch();
        }
    }
}
