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

namespace eArc\Router;

use eArc\Router\Interfaces\ResponseInterface;

class Response implements ResponseInterface
{
    public function __construct(...$argv)
    {
        foreach ($argv as $arg) {
            echo $arg;
        }
    }
}
