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

use eArc\EventTree\Interfaces\PhaseSpecificListenerInterface;
use eArc\EventTree\Interfaces\SortableListenerInterface;
use eArc\EventTree\Transformation\ObserverTree;
use eArc\Router\Interfaces\RouterListenerInterface;

abstract class AbstractController implements RouterListenerInterface, PhaseSpecificListenerInterface, SortableListenerInterface
{
    public static function getPhase(): int
    {
        return ObserverTree::PHASE_DESTINATION;
    }

    public static function getPatience(): float
    {
        return 0;
    }
}
