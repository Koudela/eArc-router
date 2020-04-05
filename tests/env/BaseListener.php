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

namespace eArc\RouterTests\env;

use eArc\Router\Interfaces\RouterEventInterface;
use eArc\Router\Interfaces\RouterListenerInterface;

class BaseListener implements RouterListenerInterface
{
    /**
     * @inheritDoc
     */
    public function process(RouterEventInterface $event): void
    {
        /** @var Collector $collector */
        $collector = di_get(Collector::class);

        $collector->calledListener[] = static::class;
    }
}
