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
 * Describes methods to grant information derived from the request. The stated
 * methods are the eArc standard methods to hand information about the request
 * to the controllers.
 */
interface RequestInformationInterface
{
    /**
     * Get the request type (GET, POST, PUT, PATCH, DELETE, CONSOLE, ...). Can
     * be different from `$_SERVER['REQUEST_METHOD']`!
     *
     * @return string
     */
    public function getType(): string;


    /**
     * Checks whether there exist a request variable with the given name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasArg(string $name): bool;

    /**
     * Get the request variable by its name. Returns the $default vale if the
     * request variable does not exist.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getArg(string $name, $default = null);

    /**
     * Get the request variables.
     *
     * @return string[]
     */
    public function getArgv() : array;
}
