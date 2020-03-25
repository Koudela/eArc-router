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

namespace eArc\RouterTests\env\routing\admin;

use eArc\EventTree\Interfaces\PhaseSpecificListenerInterface;
use eArc\EventTree\Transformation\ObserverTree;
use eArc\RouterTests\env\BaseListener;

class ListenerStart extends BaseListener implements PhaseSpecificListenerInterface
{
    public static function getPhase(): int
    {
        return ObserverTree::PHASE_START;
    }
}
