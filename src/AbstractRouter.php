<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/router
 * @link https://github.com/Koudela/earc-router/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\router;

/**
 * Describes a class that exposes methods to grant information derived from a
 * HTTP request.
 */
abstract class AbstractRouter implements \eArc\core\interfaces\LocateControllerInterface, \eArc\core\interfaces\RequestInformationInterface
{
    protected $requestType;
    protected $realArgs;
    protected $virtualArgs;
    protected $absolutePathToAccessControllers;
    protected $absolutePathToMainController;

    /**
     * @inheritDoc
     */
    final public function getRequestType(): string
    {
        return $this->requestType;
    }

    /**
     * @inheritDoc
     */
    final public function cntRealArgs(): int
    {
        return count($this->realArgs);
    }

    /**
     * @inheritDoc
     */
    final public function getRealArg(int $pos): ?string
    {
        return isset($this->realArgs[$pos]) ? $this->realArgs[$pos] : null;
    }

    /**
     * @inheritDoc
     */
    final public function getRealArgs(): array
    {
        return array_slice($this->realArgs, 0);
    }

    /**
     * @inheritDoc
     */
    final public function cntVirtualArgs(): int
    {
        return count($this->virtualArgs);
    }

    /**
     * @inheritDoc
     */
    final public function getVirtualArg(int $pos): ?string
    {
        return isset($this->virtualArgs[$pos]) ? $this->virtualArgs[$pos] : null;
    }

    /**
     * @inheritDoc
     */
    final public function getVirtualArgs(): array
    {
        return array_slice($this->virtualArgs, 0);
    }

    /**
     * @inheritDoc
     */
    final public function getAbsolutePathsToAccessControllers(): array
    {
        return array_slice($this->absolutePathToAccessControllers, 0);
    }

    /**
     * @inheritDoc
     */
    final public function getAbsolutePathToMainController(): string
    {
        if (!is_file($this->absolutePathToMainController)) {
            throw new \eArc\core\exceptions\NoControllerFoundException();
        }

        return $this->absolutePathToMainController;
    }
}
