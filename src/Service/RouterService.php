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

namespace eArc\Router\Service;

use eArc\EventTree\Interfaces\ParameterInterface;
use eArc\Router\Exceptions\IsBlacklistedException;
use eArc\Router\Exceptions\NoRouteException;

class RouterService implements ParameterInterface
{
    /**
     * @param string $fQCN
     * @param string[] $argv
     *
     * @return string
     *
     * @throws NoRouteException
     * @throws IsBlacklistedException
     */
    public static function getRoute(string $fQCN, array $argv = []): string
    {
        $blacklist = di_param(ParameterInterface::BLACKLIST);

        if (isset($blacklist[$fQCN])) {
            throw new IsBlacklistedException(sprintf('%s is blacklisted', $fQCN));
        }

        foreach (di_param(ParameterInterface::ROOT_DIRECTORIES) as $path => $namespace)
        {
            if (0 === strpos($fQCN, $namespace.'\\routing')) {
                $folders = explode('\\', substr($fQCN, strlen($namespace.'\\routing\\')));

                if (0 === count($folders)) {
                    return '/'.implode('/', $argv);
                }

                array_pop($folders);

                return implode('/', $folders).'/'.implode('/', $argv);
            }
        }

        throw new NoRouteException(sprintf('%s is no valid fully qualified controller class name.', $fQCN));
    }
}
