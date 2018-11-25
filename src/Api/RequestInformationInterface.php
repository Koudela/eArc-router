<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/router
 * @link https://github.com/Koudela/earc-router/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\Router\Api;

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
    public function getRequestType(): string;


    /**
     * Checks whether there exist a request variable with the given name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasRequestArg(string $name): bool;

    /**
     * Get the request variable by its name.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getRequestArg(string $name);

    /**
     * Get the request variables.
     *
     * @return array
     */
    public function getRequestArgs() : array;
}