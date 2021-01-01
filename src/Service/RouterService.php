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

namespace eArc\Router\Service;

use eArc\Router\Interfaces\ParameterInterface;

class RouterService implements ParameterInterface
{
    const ROUTING_EVENT_TREE_DEFAULT_DIR = 'routing';

    public static function getRoutingDir(string $fQCNEvent): string
    {
        foreach (di_param(ParameterInterface::ROUTING_DIR, []) as $fQCN => $dir) {
            if (is_a($fQCNEvent, $fQCN, true)) {
                return $dir;
            }
        }

        return RouterService::ROUTING_EVENT_TREE_DEFAULT_DIR;
    }
}
