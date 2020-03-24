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

use eArc\Observer\Interfaces\EventInterface;
use eArc\Router\AbstractController;
use eArc\Router\Interfaces\RouterEventInterface;

class Controller extends AbstractController
{
    /**
     * @inheritDoc
     */
    public function process(EventInterface $event): void
    {
        if ($event instanceof RouterEventInterface) {
            $a = 0;
            unset($a);

            var_dump('TEST');
        }
    }
}