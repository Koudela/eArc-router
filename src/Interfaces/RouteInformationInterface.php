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

namespace eArc\Router\Interfaces;

/**
 * Describes methods to grant information derived from the url. The stated
 * methods are the eArc standard methods to hand information about the route to
 * the controllers.
 */
interface RouteInformationInterface
{
    /**
     * Get the count of args related to the Controller path.
     *
     * @return integer
     */
    public function cntDirs(): int;

    /**
     * Get the arg at position $pos related to the Controller path or null if
     * there is no arg at position $pos.
     *
     * @param integer $pos
     *
     * @return string|null
     */
    public function getDir(int $pos): ?string;

    /**
     * Get a copy of the args related to the Controller path
     *
     * @return string[]
     */
    public function getDirs(): array;

    /**
     * Get the count of args not related to the Controller path.
     *
     * @return integer
     */
    public function cntParams(): int;

    /**
     * Get the arg at position $pos not related to the Controller path. Returns
     * the $default value if there is no arg at position $pos.
     *
     * @param integer     $pos
     * @param string|null $default
     *
     * @return string|null
     */
    public function getParam(int $pos, ?string $default = null): ?string;

    /**
     * Get a copy of the args not related to the Controller path
     *
     * @return string[]
     */
    public function getParams(): array;
}
