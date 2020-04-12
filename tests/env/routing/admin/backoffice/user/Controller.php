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

namespace eArc\RouterTests\env\routing\admin\backoffice\user;

use eArc\Router\Interfaces\RouterEventInterface;
use eArc\RouterTests\env\BaseController;
use eArc\RouterTests\env\Collector;

class Controller extends BaseController
{
    public function process(RouterEventInterface $event): void
    {
        parent::process($event);

        $collector = di_get(Collector::class);

        $collector->payload = [
            'route' => $event->getRoute(),
            'request' => $event->getRequest(),
        ];
    }
}
